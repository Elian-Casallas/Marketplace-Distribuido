import { Component, Inject, OnInit, PLATFORM_ID  } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import Swal from 'sweetalert2';
import { GlobalService } from '../../service/global-service.service';
import { ProductosService } from '../../service/productos.service';

@Component({
  selector: 'app-listar-productos',
  imports: [CommonModule],
  templateUrl: './listar-productos.component.html',
  styleUrl: './listar-productos.component.scss'
})
export class ListarProductosComponent {
  productos: any = [];
  pagina = 1;
  cantidad = 4;
  productosFiltrados: any = [];
  pageNumbers: any = [];
  totalPages = 0;
  cargando = true;
  constructor(
    private router: Router,
    private global: GlobalService,
    @Inject(PLATFORM_ID) private platformId: any,
    private productoService: ProductosService
  ) {}

  ngOnInit() {
    this. CargarDatos();
  }

  async CargarDatos(){
    try{
      this.cargando = true;
      const response: any = await this.productoService.GetProductosUsuario(this.global.idUsuarioFrontend);
      if (!('existe' in response)){
        console.log(response);
        const {products} = response;
        this.productos = Object.values(products).flat().sort(() => Math.random() - 0.5);
        this.productos = this.productos.map((item: any) => {
          let idFinal = item.id;
          if (typeof item.id === 'object') {
            idFinal = item.id.$oid;
          }
          return {
            ...item,
            id: idFinal,
          };
        });
        this.productosFiltrados = this.productos;
        this.updatePage();
        this.cargando = false;
      }
    }catch{
      Swal.fire({
        icon: 'error',
        title: '¡Error!',
        html:  `Productos no encontrados.`,
        allowOutsideClick: false,
        confirmButtonText: 'Ok',
      })
      .then(() => {
        this.cargando = false;
      });
    }
  }

  verProducto(id: string, categoria: string): void {
    this.router.navigate([`Ventas/Productos/editar/${id}`],{
      queryParams: { category: categoria }
    });
  }

  async eliminarProducto(idProducto: string, category: string) {
    Swal.fire({
      title: '¿Estás seguro?',
      text: 'Este producto se eliminará permanentemente.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(async (result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Eliminando...',
          text: 'Por favor, espera.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading(); // 📌 Muestra animación de carga
          }
        });
        try{
          const response: any = await this.productoService.DeleteProducto(idProducto, category);
          let mensaje = "";
          if (response){
            mensaje += '<br> Se elimino exitosamente el producto';
          }else{
            mensaje += '<br> No se elimino exitosamente el producto';
          }
          Swal.fire({
            icon: response ? 'success' : 'error',
            title: '',
            html:  mensaje,
            allowOutsideClick: false,
            confirmButtonText: 'Ok',
          })
          this.CargarDatos();
        }catch{
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            html:  `Ocurrio un error en el proces de eliminación.`,
            allowOutsideClick: false,
            confirmButtonText: 'Ok',
          })
          .then(() => {

          });
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
