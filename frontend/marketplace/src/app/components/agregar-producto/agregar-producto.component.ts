import { Component, Inject, OnInit, PLATFORM_ID  } from '@angular/core';
import { Router } from '@angular/router';
import Swal from 'sweetalert2';
import { GlobalService } from '../../service/global-service.service';
import { ProductosService } from '../../service/productos.service';
import { CommonModule } from '@angular/common';
import { UsuariosService } from '../../service/usuarios-service.service';

@Component({
  selector: 'app-agregar-producto',
  imports: [CommonModule],
  templateUrl: './agregar-producto.component.html',
  styleUrl: './agregar-producto.component.scss'
})
export class AgregarProductoComponent {

  cargando = true;

  constructor(
    private router: Router,
    private global: GlobalService,
    @Inject(PLATFORM_ID) private platformId: any,
    private productoService: ProductosService,
    private usuarioService: UsuariosService,
  ) {}

  async ngOnInit() {
    try{
      this.cargando = true;
      const response: any = await this.usuarioService.Get(this.global.idUsuarioFrontend, false);
      console.log(response)
      if (!('existe' in response)){
        const {isSeller} = response;
        if (!isSeller){
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            html:  `Solo los usuarios vendedores pueden agregar productos.<br><br>Por favor, conviértase en vendedor para acceder a esta función.`,
            allowOutsideClick: false,
            confirmButtonText: 'Ok',
          }).then(() => {
            this.router.navigate(['usuario/${this.global.idUsuarioFrontend}']);
          });
        }
        this.cargando = false;
      }
    }catch{
      Swal.fire({
        icon: 'error',
        title: '¡Error!',
        html:  `Error en el servidor.`,
        allowOutsideClick: false,
        confirmButtonText: 'Ok',
      })
      this.cargando = false;
    }
  }

  async onAgregar(event: Event, name: string, link: string, precio: string, cantidad: string, descripcion: string) {
    event.preventDefault();
    try{
      const form = event.target as HTMLFormElement;
      const tipo = (form.elements.namedItem('tipo') as HTMLSelectElement).value;
      Swal.fire({
        title: "Guardando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      const userData = { name, link, category: tipo, price: parseInt(precio), stock: parseInt(cantidad), description: descripcion, seller_id: this.global.idUsuarioFrontend, attributes: []};
      const response: any = await this.productoService.Guardar(userData);
      if (response){
        Swal.fire('', 'El producto a sido creado exitosamente', 'success')
        .then(() => {
          this.router.navigate(['Ventas/Productos/Listar/']);
        });
      }
    }catch(error) {
      console.error('Error al guardar producto:', error);
    }
  }
}
