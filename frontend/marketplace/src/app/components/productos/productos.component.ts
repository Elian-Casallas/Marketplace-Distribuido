import { Component,  Inject, OnInit, PLATFORM_ID  } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import Swal from 'sweetalert2';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { GlobalService } from '../../service/global-service.service';
import { ProductosService } from '../../service/productos.service';

@Component({
  selector: 'app-productos',
  imports: [CommonModule, FormsModule],
  templateUrl: './productos.component.html',
  styleUrl: './productos.component.scss'
})
export class ProductosComponent implements OnInit {
  productoArray: any = [];
  productosFiltrados: any = [];
  busqueda: string = '';
  //
  cargando = true;

  textoBusqueda: string = '';
  tipoSeleccionado: string = 'all';
  tipos: any = [{
    text: 'Todos',
    value: 'all'
  }, {
    text: 'Ropa',
    value: 'clothes'
  }, {
    text: 'Electrónica',
    value: 'electronics'
  }, {
    text: 'Hogar',
    value: 'home'
  }];

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    @Inject(PLATFORM_ID) private platformId: any,
    private global: GlobalService,
    private productoService: ProductosService,
  ) {}

  async ngOnInit() {
    try{
      this.route.queryParams.subscribe(params => {
        this.busqueda = params['search'] ?? '';
      });
      const response: any = await this.productoService.Get();
      if ('existe' in response){
        this.ErrorMensaje();
      }else{
        const {products} = response;
        console.log(products);
        this.productoArray = products;
        this.productosFiltrados = Object.values(products).flat().sort(() => Math.random() - 0.5);
        this.productosFiltrados = this.productosFiltrados.filter((item: any) => item.stock > 0).map((item: any) => {
          let idFinal = item.id;
          if (typeof item.id === 'object') {
            idFinal = item.id.$oid;
          }
          return {
            ...item,
            id: idFinal,
          };
        });
        console.log(this.productosFiltrados)
        if (this.busqueda && this.busqueda.trim() !== '') {
          const term = this.busqueda.toLowerCase();
          const filtro = this.productosFiltrados.filter((p: any) =>
            p.name.toLowerCase().includes(term) ||
            p.description?.toLowerCase().includes(term)
          );
          this.productosFiltrados = filtro;
        }
        console.log(this.productosFiltrados);
        Swal.fire({
          icon: 'success',
          title: '',
          html: 'Gracias por esperar ',
          allowOutsideClick: false,
          confirmButtonText: 'Ok',
        })
        this.cargando = false;
      }
    }catch{
      this.ErrorMensaje();
    }
  }

  ErrorMensaje() {
    Swal.fire({
      icon: 'error',
      title: '¡Error!',
      html:  `Producto no encontrado.`,
      allowOutsideClick: false,
      confirmButtonText: 'Ok',
    })
  }

  filtrar() {
    const tipo = this.tipoSeleccionado;
    if (tipo === 'all') {
      this.productosFiltrados = Object.values(this.productoArray).flat().filter((item: any) => item.stock > 0).sort(() => Math.random() - 0.5);
    } else {
      const productos = this.productoArray[tipo] || [];
      this.productosFiltrados = productos.filter((item: any) => item.stock > 0).sort(() => Math.random() - 0.5);
    }
  }
}
