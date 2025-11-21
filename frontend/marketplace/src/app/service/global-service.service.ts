import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class GlobalService {
  private readonly STORAGE_KEY = 'carrito';
  public idUsuarioFrontend: string | null = null;
  public nombreUsuarioFrontend: string | null = '';
  private productoSubject = new BehaviorSubject<any[]>([]);
  public producto$ = this.productoSubject.asObservable();

  constructor() { }

  public cargarDesdeLocalStorage(): any {
    const data = localStorage.getItem(this.STORAGE_KEY);
    if (data){
      this.productoSubject.next(JSON.parse(data));
    }
  }

  private guardarEnLocalStorage(data: any[]) {
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
  }

  // Agrega un nuevo elemento
  agregarElemento(nuevo: any) {
    // 1. Leer lo que ya existe
    const actual = JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');

    // 2. Agregar el nuevo elemento respetando lo anterior
    actual.push(nuevo);

    // 3. Guardar nuevamente
    this.guardarEnLocalStorage(actual);

    // 4. Actualizar el BehaviorSubject
    this.productoSubject.next(actual);
  }

  // Elimina por ID, índice o propiedad específica
  eliminarElementoPorId(id: any) {
    const actual = this.productoSubject.value;
    const nuevo = actual.filter(e => e.id !== id);
    this.productoSubject.next(nuevo);
    this.guardarEnLocalStorage(nuevo);
  }

  limpiarLista() {
    this.productoSubject.next([]);
    this.guardarEnLocalStorage([]);
  }
}
