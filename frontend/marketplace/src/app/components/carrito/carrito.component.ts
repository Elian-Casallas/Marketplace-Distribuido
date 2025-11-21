import { Component, Inject, OnInit, PLATFORM_ID  } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import Swal from 'sweetalert2';
import { GlobalService } from '../../service/global-service.service';
import { ProductosService } from '../../service/productos.service';

@Component({
  selector: 'app-carrito',
  imports: [CommonModule],
  templateUrl: './carrito.component.html',
  styleUrl: './carrito.component.scss'
})
export class CarritoComponent {
  productos: any = [];
  pagina = 1;
  cantidad = 10;
  productosFiltrados: any = [];
  pageNumbers: any = [];
  totalPages = 0;
  total: any = 0;
  recargar: boolean = true;
  cargando = false;
  constructor(
    private router: Router,
    private global: GlobalService,
    @Inject(PLATFORM_ID) private platformId: any,
    private productoService: ProductosService
  ) {}

  ngOnInit() {
    this.global.producto$.subscribe((producto) => {
      if (this.recargar){
        this.CargarDatos(producto);
        this.recargar = false;
      }
    });
  }

  async CargarDatos(producto: any){
    try{
      this.cargando = true;
      const productosBuscar: any = producto.reduce((acc: any[], id: any) => {
        const existente = acc.find(p => p.id.id === id.id);

        if (existente) {
          existente.cantidad += 1;
        } else {
          acc.push({ id: id, cantidad: 1 });
        }

        return acc;
      }, []);
      // Crear una nueva estructura aplanada
      const productosLimpios = productosBuscar.map((p: any) => ({
        id: p.id.id,
        category: p.id.category,
        cantidad: p.cantidad
      }));
      const response: any = await this.productoService.GetProductosCarrito(productosLimpios);
      if (!('existe' in response)){
        const {products} = response;
        this.productos = products.map((item: any) => {
          // Si viene desde backup
          if (item.product) {
            return {
              ...item.product,     // todos los datos del producto directo
              cantidad: item.cantidad,
              total: item.cantidad * item.product.price
            };
          }
          // Si viene normal desde el nodo
          return {
            ...item,
            cantidad: item.cantidad,
            total: item.cantidad * item.price
          };
        });
        console.log(this.productos)
        // 2. Calcular el gran total
        this.total = this.productos.reduce((sum: number, p: any) => sum + p.total, 0);
        this.cargando = false;
        this.updatePage();
      }
    }catch{
      this.cargando = false;
      Swal.fire({
        icon: 'error',
        title: '¡Error!',
        html:  `Productos no encontrados.`,
        allowOutsideClick: false,
        confirmButtonText: 'Ok',
      });
    }
  }

  eliminar(id: string): void {
    this.productos = this.productos.filter((p: any) => {
      if (id === p.id){
        this.total -= p.total;
      }
      return id !== p.id;
    });
    this.global.eliminarElementoPorId(id);
    this.updatePage();
  }

  pagar(): void {
    Swal.fire({
      title: '¿Estás seguro de realizar el pago?',
      text: '',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'Cancelar'
    }).then(async (result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: "Guardando...",
          text: 'Por favor, espera.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading(); // 📌 Muestra animación de carga
          }
        });
        try{
          const productos = this.productosFiltrados.filter((item: any) => item.stock - item.cantidad > 0)
                            .map((item: any) => ({
                              id: item.id,
                              stock: item.stock - item.cantidad,
                              category: item.category
                            }));
          const response: any = await this.productoService.ActualizarStore(productos);
          if (response){
            Swal.fire('', 'El producto a sido actualizado exitosamente', 'success')
            .then(() => {
              this.global.limpiarLista();
              this.router.navigate(['Ventas/Productos/Listar/']);
            });
          }
        }catch(error) {
          console.error('Error al guardar producto:', error);
        }
      }
    });
  }

  updatePage() {
    const indexLast = this.pagina * this.cantidad;
    const indexFirst = indexLast - this.cantidad;
    this.productosFiltrados = this.productos.slice(indexFirst, indexLast);

    const totalBlogs = this.productos.length;
    this.totalPages = Math.ceil(totalBlogs / this.cantidad);
    this.pageNumbers = [];
    for (let i = 1; i <= this.totalPages; i++) {
      this.pageNumbers.push(i);
    }
  }

  // Función para manejar la paginación
  paginacion(numPage: number) {
    this.pagina = numPage;
    this.updatePage();
  }
}
