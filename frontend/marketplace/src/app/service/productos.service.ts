import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ProductosService {
  private apiUrl = environment.mainUrl;
    private headers = {
      'X-Internal-Key': environment.keyMain,
      'Accept': 'application/json'
    };

  constructor(private http: HttpClient) {}

  async Get() {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const productos: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/gateway/productos`, {
          headers: this.headers,
        }),
      );
      return productos;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async GetProductosUsuario(idUsuario: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const productos: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/gateway/productos/usuario/${idUsuario}`, {
          headers: this.headers,
        }),
      );
      return productos;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async GetProducto(idProducto: any, category: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const producto: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/gateway/products/${idProducto}`, {
          headers: this.headers, params: { category }
        }),
      );
      return producto;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async GetProductos(category: any, id: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const productos: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/gateway/recommended/products/${id}`, {
          headers: this.headers, params: { category }
        }),
      );
      return productos;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async GetProductosCategory(category: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const productos: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/gateway/products`, {
          headers: this.headers, params: { category }
        }),
      );
      return productos;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async GetProductosCarrito(productosAll: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const productos: any = await firstValueFrom(
        this.http.post(`${this.apiUrl}/gateway/products/bulk`, {products: productosAll}, {
          headers: this.headers
        }),
      );
      return productos;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async Guardar(data: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const producto: any = await firstValueFrom(
        this.http.post(`${this.apiUrl}/gateway/products`, data, {
          headers: this.headers,
        }),
      );
      return producto;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async DeleteProducto(idProducto: any, category: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const producto: any = await firstValueFrom(
        this.http.delete(`${this.apiUrl}/gateway/products/${idProducto}`, {
          headers: this.headers, params: {
            category: category
          }
        }),
      );
      return producto;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async Actualizar(data: any, id: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const producto: any = await firstValueFrom(
        this.http.put(`${this.apiUrl}/gateway/products/${id}`, data, {
          headers: this.headers,
        }),
      );
      return producto;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async ActualizarStore(products: any) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const producto: any = await firstValueFrom(
        this.http.put(`${this.apiUrl}/gateway/prod/store`, {"products": products}, {
          headers: this.headers,
        }),
      );
      return producto;
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }
}
