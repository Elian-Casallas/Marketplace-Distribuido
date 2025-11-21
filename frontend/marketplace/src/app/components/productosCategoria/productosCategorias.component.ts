import { Component,  Inject, OnInit, PLATFORM_ID  } from '@angular/core';
import { Router, ActivatedRoute} from '@angular/router';
import Swal from 'sweetalert2';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { GlobalService } from '../../service/global-service.service';
import { ProductosService } from '../../service/productos.service';

@Component({
  selector: 'app-productos',
  imports: [CommonModule, FormsModule],
  templateUrl: './productosCategorias.component.html',
  styleUrl: './productosCategorias.component.scss'
})
export class productosCategoriasComponent implements OnInit {
  category!: string;
  //
  productosArray: any = [];
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
  ) {
    this.route.params.subscribe(params => {
      this.category = params['category'];
    });
  }

  async ngOnInit() {
    try{
      const response: any = await this.productoService.GetProductosCategory(this.category);
      if ('existe' in response){
        this.ErrorMensaje();
      }else{
        console.log(response);
        if ('products' in response) {
          const {products} = response;
          this.productosArray = products.filter((item: any) => item.stock > 0).map((item: any) => {
            let idFinal = item.id;
            if (typeof item.id === 'object') {
              idFinal = item.id.$oid;
            }
            return {
              ...item,
              id: idFinal,
            };
          });
        }else{
          this.productosArray = response.filter((item: any) => item.stock > 0);
        }
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
}
