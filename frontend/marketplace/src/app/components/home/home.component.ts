import { Component, Inject, OnInit, PLATFORM_ID, ViewChild, ElementRef  } from '@angular/core';
import { Router, ActivatedRoute} from '@angular/router';
import { CommonModule } from '@angular/common';
import Swal from 'sweetalert2';
import { ProductosService } from '../../service/productos.service';
import { GlobalService } from '../../service/global-service.service';

@Component({
  selector: 'app-home',
  imports: [CommonModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent {

  productoArray: any = [];
  productoArray2: any = [];
  productoArray3: any = [];
  cargandoRecomendados = true;
  cargandoRecomendados2 = true;
  cargandoRecomendados3 = true;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    @Inject(PLATFORM_ID) private platformId: any,
    private productoService: ProductosService,
    private global: GlobalService
  ) {}

  async ngOnInit() {
    this.InformacionProductos('clothes', 1);
    this.InformacionProductos('electronics', 2);
    this.InformacionProductos('home', 3);
  }

  async InformacionProductos($category: string, $num: any){
    try{
      if ($num === 1){
        this.cargandoRecomendados = true;
      }else if($num === 2){
        this.cargandoRecomendados2 = true;
      }else{
        this.cargandoRecomendados3 = true;
      }
      const response: any = await this.productoService.GetProductos($category, 'a');
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
          if ($num === 1){
            this.productoArray = cong;
          }else if($num === 2){
            this.productoArray2 = cong;
          }else{
            this.productoArray3 = cong;
          }
        }else{
          const cong = response.filter((item: any) => item.stock > 0);
          if ($num === 1){
            this.productoArray = cong;
          }else if($num === 2){
            this.productoArray2 = cong;
          }else{
            this.productoArray3 = cong;
          }
        }
        if ($num === 1){
          this.cargandoRecomendados = false;
        }else if($num === 2){
          this.cargandoRecomendados2 = false;
        }else{
          this.cargandoRecomendados3 = false;
        }
      }
    }catch{
      console.log("error")
    }
  }
}
