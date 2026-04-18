import { Injectable, signal, computed } from '@angular/core';
import { Observable, from, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { ImageUpload, ImageConversion } from '../models/catalog.model';

@Injectable({
  providedIn: 'root'
})
export class ImageService {
  // Signaux pour l'état du traitement d'images
  private readonly processingImages = signal<number>(0);
  private readonly maxProcessingImages = signal<number>(5);
  private readonly isProcessing = computed(() => this.processingImages() > 0);

  // Signaux exposés publiquement
  readonly isProcessing$ = of(this.isProcessing());
  readonly processingCount$ = of(this.processingImages());

  // Configuration pour les images
  private readonly IMAGE_CONFIG = {
    MAX_SIZE: 5 * 1024 * 1024, // 5MB par image
    MAX_WIDTH: 1920,
    MAX_HEIGHT: 1080,
    QUALITY: 0.8,
    THUMBNAIL_WIDTH: 300,
    THUMBNAIL_HEIGHT: 300,
    THUMBNAIL_QUALITY: 0.6,
    SUPPORTED_FORMATS: ['image/jpeg', 'image/png', 'image/webp']
  };

  /**
   * Convertit un fichier File en base64 avec compression
   */
  convertFileToBase64(file: File): Observable<ImageConversion> {
    this.processingImages.update(count => count + 1);

    return from(this.processImage(file)).pipe(
      map(result => {
        this.processingImages.update(count => count - 1);
        return result;
      }),
      catchError(error => {
        this.processingImages.update(count => count - 1);
        console.error('Error converting image:', error);
        throw error;
      })
    );
  }

  /**
   * Convertit plusieurs fichiers en base64
   */
  convertMultipleFilesToBase64(files: File[]): Observable<ImageConversion[]> {
    const conversions = files.map(file => this.convertFileToBase64(file));
    
    return from(Promise.all(conversions.map(conv => conv.toPromise() as Promise<ImageConversion>)));
  }

  /**
   * Valide un fichier image
   */
  validateImageFile(file: File): { isValid: boolean; error?: string } {
    // Vérifier le type MIME
    if (!this.IMAGE_CONFIG.SUPPORTED_FORMATS.includes(file.type)) {
      return {
        isValid: false,
        error: `Format non supporté. Formats acceptés: ${this.IMAGE_CONFIG.SUPPORTED_FORMATS.join(', ')}`
      };
    }

    // Vérifier la taille
    if (file.size > this.IMAGE_CONFIG.MAX_SIZE) {
      return {
        isValid: false,
        error: `Fichier trop volumineux. Taille maximale: ${this.formatFileSize(this.IMAGE_CONFIG.MAX_SIZE)}`
      };
    }

    return { isValid: true };
  }

  /**
   * Crée une URL d'aperçu pour une image base64
   */
  createPreviewUrl(base64: string): string {
    if (!base64) return '';
    
    // Si c'est déjà une URL complète, la retourner
    if (base64.startsWith('http')) return base64;
    
    // Si c'est du base64, créer une URL blob
    if (base64.startsWith('data:image')) return base64;
    
    // Sinon, créer une URL blob à partir du base64
    try {
      const byteCharacters = atob(base64);
      const byteNumbers = new Array(byteCharacters.length);
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
      }
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: 'image/jpeg' });
      return URL.createObjectURL(blob);
    } catch (error) {
      console.error('Error creating preview URL:', error);
      return '';
    }
  }

  /**
   * Nettoie les URLs d'aperçu pour éviter les fuites de mémoire
   */
  revokePreviewUrl(url: string): void {
    if (url.startsWith('blob:')) {
      URL.revokeObjectURL(url);
    }
  }

  /**
   * Traitement principal d'une image
   */
  private async processImage(file: File): Promise<ImageConversion> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      
      reader.onload = (event) => {
        const img = new Image();
        
        img.onload = () => {
          try {
            // Créer le canvas pour le redimensionnement
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            if (!ctx) {
              reject(new Error('Impossible de créer le contexte canvas'));
              return;
            }

            // Calculer les nouvelles dimensions
            const { width, height } = this.calculateDimensions(
              img.width, 
              img.height, 
              this.IMAGE_CONFIG.MAX_WIDTH, 
              this.IMAGE_CONFIG.MAX_HEIGHT
            );

            // Redimensionner l'image principale
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);
            
            const base64 = canvas.toDataURL('image/jpeg', this.IMAGE_CONFIG.QUALITY);

            // Créer la miniature si nécessaire
            let thumbnail: string | undefined;
            if (width > this.IMAGE_CONFIG.THUMBNAIL_WIDTH || height > this.IMAGE_CONFIG.THUMBNAIL_HEIGHT) {
              const thumbCanvas = document.createElement('canvas');
              const thumbCtx = thumbCanvas.getContext('2d');
              
              if (thumbCtx) {
                const { width: thumbWidth, height: thumbHeight } = this.calculateDimensions(
                  width, 
                  height, 
                  this.IMAGE_CONFIG.THUMBNAIL_WIDTH, 
                  this.IMAGE_CONFIG.THUMBNAIL_HEIGHT
                );
                
                thumbCanvas.width = thumbWidth;
                thumbCanvas.height = thumbHeight;
                thumbCtx.drawImage(canvas, 0, 0, thumbWidth, thumbHeight);
                thumbnail = thumbCanvas.toDataURL('image/jpeg', this.IMAGE_CONFIG.THUMBNAIL_QUALITY);
              }
            }

            resolve({
              original: file,
              base64,
              thumbnail
            });
          } catch (error) {
            reject(error);
          }
        };

        img.onerror = () => reject(new Error('Erreur lors du chargement de l\'image'));
        img.src = event.target?.result as string;
      };

      reader.onerror = () => reject(new Error('Erreur lors de la lecture du fichier'));
      reader.readAsDataURL(file);
    });
  }

  /**
   * Calcule les dimensions en conservant le ratio
   */
  private calculateDimensions(
    originalWidth: number, 
    originalHeight: number, 
    maxWidth: number, 
    maxHeight: number
  ): { width: number; height: number } {
    const ratio = Math.min(maxWidth / originalWidth, maxHeight / originalHeight);
    
    if (ratio >= 1) {
      return { width: originalWidth, height: originalHeight };
    }

    return {
      width: Math.round(originalWidth * ratio),
      height: Math.round(originalHeight * ratio)
    };
  }

  /**
   * Formate la taille d'un fichier
   */
  private formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  /**
   * Extrait les informations d'une image base64
   */
  extractImageInfo(base64: string): { mimeType: string; size: number } | null {
    try {
      // Extraire le type MIME
      const mimeTypeMatch = base64.match(/^data:(image\/\w+);base64,/);
      if (!mimeTypeMatch) return null;
      
      const mimeType = mimeTypeMatch[1];
      
      // Calculer la taille approximative
      const base64Data = base64.replace(/^data:image\/\w+;base64,/, '');
      const size = Math.round(base64Data.length * 0.75); // Approximation base64 -> bytes
      
      return { mimeType, size };
    } catch (error) {
      console.error('Error extracting image info:', error);
      return null;
    }
  }

  /**
   * Vérifie si une image base64 est valide
   */
  isValidBase64Image(base64: string): boolean {
    try {
      if (!base64.startsWith('data:image/')) return false;
      
      const base64Data = base64.replace(/^data:image\/\w+;base64,/, '');
      
      // Vérifier que c'est du base64 valide
      return /^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)?$/.test(base64Data);
    } catch (error) {
      return false;
    }
  }
}
