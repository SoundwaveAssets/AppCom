import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly baseUrl = environment.api.baseUrl;

  constructor(private http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });

    return headers;
  }

  get<T>(endpoint: string, params?: any): Observable<T> {
    const headers = this.getHeaders();
    
    let httpParams = new HttpParams();
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
          httpParams = httpParams.set(key, params[key]);
        }
      });
    }

    return this.http.get<T>(`${this.baseUrl}${endpoint}`, {
      headers,
      params: httpParams
    });
  }

  post<T>(endpoint: string, data?: any): Observable<T> {
    const headers = this.getHeaders();
    
    return this.http.post<T>(`${this.baseUrl}${endpoint}`, data, {
      headers
    });
  }

  patch<T>(endpoint: string, data?: any): Observable<T> {
    const headers = this.getHeaders();
    
    return this.http.patch<T>(`${this.baseUrl}${endpoint}`, data, {
      headers
    });
  }

  delete<T>(endpoint: string): Observable<T> {
    const headers = this.getHeaders();
    
    return this.http.delete<T>(`${this.baseUrl}${endpoint}`, {
      headers
    });
  }
}
