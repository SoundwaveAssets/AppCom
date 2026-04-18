import { Component, signal, computed, input, output, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatCardModule } from '@angular/material/card';
import { MatSnackBar } from '@angular/material/snack-bar';

import { ImageService } from '../../../core/services/image.service';
import { ImageConversion, ImageUpload } from '../../../core/models/catalog.model';

@Component({
  selector: 'app-image-upload',
  standalone: true,
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule,
    MatProgressSpinnerModule,
    MatCardModule
  ],
  templateUrl: './image-upload.component.html',
  styleUrls: ['./image-upload.component.scss']
})
export class ImageUploadComponent {
  private readonly imageService = inject(ImageService);
  private readonly snackBar = inject(MatSnackBar);

  // Inputs
  readonly maxImages = input<number>(5);
  readonly acceptTypes = input<string>('image/jpeg,image/png,image/webp');
  readonly maxSize = input<number>(5 * 1024 * 1024); // 5MB
  readonly showPreview = input<boolean>(true);
  readonly existingImages = input<string[]>([]);

  // Outputs
  readonly imagesChange = output<string[]>();
  readonly uploadStart = output<void>();
  readonly uploadComplete = output<ImageConversion[]>();
  readonly uploadError = output<Error>();

  // Signaux internes
  private readonly selectedFiles = signal<File[]>([]);
  private readonly processedImages = signal<ImageConversion[]>([]);
  private readonly isUploading = signal<boolean>(false);
  private readonly dragOver = signal<boolean>(false);

  // Signaux calculés
  readonly currentImages = computed(() => {
    const existing = this.existingImages() || [];
    const processed = this.processedImages().map(img => img.base64);
    return [...existing, ...processed];
  });

  readonly canAddMore = computed(() => {
    return this.currentImages().length < this.maxImages();
  });

  readonly uploadProgress = computed(() => {
    return this.processedImages().length + this.selectedFiles().length;
  });

  // Signaux exposés pour le template
  readonly isDragOver = this.dragOver.asReadonly();
  readonly isUploadInProgress = this.isUploading.asReadonly();

  onFileSelect(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;

    const files = Array.from(input.files);
    this.processFiles(files);
    
    // Reset input value pour permettre de sélectionner le même fichier
    input.value = '';
  }

  onDragOver(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.dragOver.set(true);
  }

  onDragLeave(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.dragOver.set(false);
  }

  onDrop(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.dragOver.set(false);

    const files = Array.from(event.dataTransfer?.files || []);
    this.processFiles(files);
  }

  removeImage(index: number): void {
    const current = this.currentImages();
    const existingCount = (this.existingImages() || []).length;
    
    if (index < existingCount) {
      // Supprimer une image existante
      const existing = [...(this.existingImages() || [])];
      existing.splice(index, 1);
      // Mettre à jour via un callback ou signal
      this.imagesChange.emit([...existing, ...this.processedImages().map(img => img.base64)]);
    } else {
      // Supprimer une image nouvellement uploadée
      const processedIndex = index - existingCount;
      const processed = [...this.processedImages()];
      processed.splice(processedIndex, 1);
      this.processedImages.set(processed);
      this.emitImagesChange();
    }
  }

  removeAllImages(): void {
    this.processedImages.set([]);
    this.selectedFiles.set([]);
    this.emitImagesChange();
  }

  private processFiles(files: File[]): void {
    const validFiles = files.filter(file => this.validateFile(file));
    
    if (validFiles.length === 0) return;

    // Vérifier la limite d'images
    const currentCount = this.currentImages().length;
    const availableSlots = this.maxImages() - currentCount;
    
    if (availableSlots <= 0) {
      this.snackBar.open(`Nombre maximum d'images atteint (${this.maxImages()})`, 'Fermer', {
        duration: 3000
      });
      return;
    }

    const filesToProcess = validFiles.slice(0, availableSlots);
    this.selectedFiles.set(filesToProcess);
    
    this.uploadImages(filesToProcess);
  }

  private validateFile(file: File): boolean {
    const validation = this.imageService.validateImageFile(file);
    
    if (!validation.isValid) {
      this.snackBar.open(validation.error || 'Fichier invalide', 'Fermer', {
        duration: 3000
      });
      return false;
    }

    return true;
  }

  private async uploadImages(files: File[]): Promise<void> {
    if (files.length === 0) return;

    this.isUploading.set(true);
    this.uploadStart.emit();

    try {
      const conversions = await this.imageService.convertMultipleFilesToBase64(files).toPromise();
      
      if (conversions) {
        const currentProcessed = this.processedImages();
        this.processedImages.set([...currentProcessed, ...conversions]);
        this.emitImagesChange();
        this.uploadComplete.emit(conversions);
      }
    } catch (error) {
      console.error('Error uploading images:', error);
      this.snackBar.open('Erreur lors du traitement des images', 'Fermer', {
        duration: 3000
      });
      this.uploadError.emit(error as Error);
    } finally {
      this.isUploading.set(false);
      this.selectedFiles.set([]);
    }
  }

  private emitImagesChange(): void {
    const allImages = this.currentImages();
    this.imagesChange.emit(allImages);
  }

  getImagePreview(base64: string): string {
    return this.imageService.createPreviewUrl(base64);
  }

  getImageName(base64: string): string {
    const info = this.imageService.extractImageInfo(base64);
    return info?.mimeType.split('/')[1]?.toUpperCase() || 'Image';
  }

  getImageSize(base64: string): string {
    const info = this.imageService.extractImageInfo(base64);
    if (!info?.size) return '';
    
    const bytes = info.size;
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  }

  onImageError(event: Event): void {
    const img = event.target as HTMLImageElement;
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMCAyNkMxOC4yMDYxIDI2IDE2LjUgMjQuMjkzOSAxNi41IDIyQzE2LjUgMTkuNzA2MSAxOC4yMDYxIDE4IDIwIDE4QzIxLjc5MzkgMTggMjMuNSAxOS43MDYxIDIzLjUgMjJDMjMuNSAyNC4yOTM5IDIxLjc5MzkgMjYgMjAgMjZaIiBmaWxsPSIjOTk5OTk5Ii8+CjxwYXRoIGQ9Ik0yMCAxNkMyMS4xMDQ2IDE2IDIyIDE2Ljg5NTQgMjIgMThIMjBWMjBIMThWMThWMTZIMjBaTTIwIDE2SDIwVjE2SDIwWiIgZmlsbD0iIzk5OTk5OSIvPgo8L3N2Zz4K';
  }

  ngOnDestroy(): void {
    // Nettoyer les URLs d'aperçu pour éviter les fuites de mémoire
    this.currentImages().forEach(base64 => {
      this.imageService.revokePreviewUrl(this.getImagePreview(base64));
    });
  }
}
