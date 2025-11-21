import { Component, Inject, OnInit, PLATFORM_ID, ViewChild, ElementRef  } from '@angular/core';
import { Router, ActivatedRoute} from '@angular/router';
import { CommonModule } from '@angular/common';
import Swal from 'sweetalert2';
import { ProductosService } from '../../service/productos.service';
import { GlobalService } from '../../service/global-service.service';

@Component({
  selector: 'app-producto',
  imports: [CommonModule],
  templateUrl: './producto.component.html',
  styleUrl: './producto.component.scss'
})
export class ProductoComponent implements OnInit {

  idProducto: any;
  category!: string;
  link: any;
  name: any;
  precio: any;
  descripcion: any;
  productoArray: any = [];
  stock: any;
  //
  cargando = true;
  cargandoRecomendados = true;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    @Inject(PLATFORM_ID) private platformId: any,
    private productoService: ProductosService,
    private global: GlobalService
  ) {
    this.route.params.subscribe(params => {
      this.idProducto = params['id'];
      this.category = params['category'];
    });
  }

  async ngOnInit() {
    this.InformacionProducto();
    this.InformacionProductos();
  }

  async InformacionProducto(){
    try{
      this.cargando = true;
      const response: any = await this.productoService.GetProducto(this.idProducto, this.category);
      console.log(response)
      if ('existe' in response){
        this.ErrorMensaje();
      }else{
        console.log(response)
        const {link, name, price, description, stock} = response;
        this.link = link;
        this.name = name;
        this.precio = price;
        this.descripcion = description;
        this.cargando = false;
        this.stock = stock;
        if (stock <= 0){
          Swal.fire({
            icon: 'warning',
            title: '',
            html:  `El producto ya no tenemos en stock.`,
            allowOutsideClick: false,
            confirmButtonText: 'Ok',
          }).then(() => {
            this.router.navigate(['/']);
          })
        }
      }
    }catch{
      this.ErrorMensaje();
    }
  }

  async InformacionProductos(){
    try{
      this.cargandoRecomendados = true;
      const response: any = await this.productoService.GetProductos(this.category, this.idProducto);
      if (!('existe' in response)){
        if ('products' in response) {
          const {products} = response;
          const cong = products.filter((item: any) => item.stock > 0).map((item: any) => {
            let idFinal = item.id;
            if (typeof item.id === 'object') {
              idFinal = item.id.$oid;
            }
            return {
              ...item,
              id: idFinal,
            };
          });
          this.productoArray = cong.filter((producto: any) => {
            return producto.id !== this.idProducto;
          })
        }else{
          this.productoArray = response.filter((item: any) => item.stock > 0).filter((producto: any) => {
            return producto.id !== this.idProducto;
          })
        }
        this.cargandoRecomendados = false;
      }
    }catch{
      console.log("error")
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

  agregar() {
    if (this.stock - 1 > 0){
      if (!this.global.idUsuarioFrontend){
        Swal.fire({
          icon: 'warning',
          title: '',
          html: 'Para entrar necesitas una cuenta, por favor crea o entra en sesión en una cuenta para continuar',
          allowOutsideClick: false,
          confirmButtonText: 'Ok',
        }).then(() => this.router.navigate(['/login']));
        return;
      }
      this.global.agregarElemento({
        id: this.idProducto,
        category: this.category
      });
      Swal.fire('', 'El producto a sido agregado exitosamente.', 'success');
    }else{
      Swal.fire({
        icon: 'warning',
        title: '',
        html:  `El producto ya no tenemos en stock.`,
        allowOutsideClick: false,
        confirmButtonText: 'Ok',
      })
    }
  }

}
