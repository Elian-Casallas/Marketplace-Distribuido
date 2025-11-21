import { Component, ElementRef, Inject, OnInit, PLATFORM_ID, ViewChild  } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import Swal from 'sweetalert2';
import { GlobalService } from '../../service/global-service.service';
import { ProductosService } from '../../service/productos.service';
import { CommonModule } from '@angular/common';
import { UsuariosService } from '../../service/usuarios-service.service';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-editar-productos',
  imports: [FormsModule, CommonModule],
  templateUrl: './editar-productos.component.html',
  styleUrl: './editar-productos.component.scss'
})
export class EditarProductosComponent {
  cargando = true;
  productoId: string = '';
  category!: string;

  constructor(
    private router: Router,
    private global: GlobalService,
    private route: ActivatedRoute,
    @Inject(PLATFORM_ID) private platformId: any,
    private productoService: ProductosService,
    private usuarioService: UsuariosService,
  ) {
    this.route.params.subscribe(params => {
      this.productoId = params['id'];
    });
  }

  async ngOnInit() {
    try{
      this.cargando = true;
      this.route.queryParams.subscribe(params => {
        this.category = params['category'] ?? '';
      });
      Swal.fire({
        title: "Buscando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      const response: any = await this.productoService.GetProducto(this.productoId, this.category);
      if (!('existe' in response)){
        const {name, link, category, price, stock, description} = response;
        console.log(response)
        const nameInput: HTMLSelectElement | any = document.getElementById('name');
        nameInput.value = name;
        const linkInput: HTMLSelectElement | any = document.getElementById('link');
        linkInput.value = link;
        const precioInput: HTMLSelectElement | any = document.getElementById('precio');
        precioInput.value = price;
        const cantidadInput: HTMLSelectElement | any = document.getElementById('cantidad');
        cantidadInput.value = stock;
        const descripcionInput: HTMLSelectElement | any = document.getElementById('descripcion');
        descripcionInput.value = description;
        const tipoSelect: HTMLSelectElement | any = document.getElementById('tipo');
        tipoSelect.value = category;
        Swal.fire({
          icon: 'success',
          title: '',
          html: 'Producto encontrado',
          allowOutsideClick: false,
          confirmButtonText: 'Ok',
        })
      }
    }catch{
      Swal.fire({
        icon: 'error',
        title: '¡Error!',
        html:  `Error en el servidor.`,
        allowOutsideClick: false,
        confirmButtonText: 'Ok',
      })
    }
  }

  async onEdit(event: Event, name: string, link: string, precio: string, cantidad: string, descripcion: string) {
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
      const userData = { name, link, category: tipo, price: parseInt(precio), stock: parseInt(cantidad), description: descripcion};
      const response: any = await this.productoService.Actualizar(userData, this.productoId);
      if (response){
        Swal.fire('', 'El producto a sido actualizado exitosamente', 'success')
        .then(() => {
          this.router.navigate(['Ventas/Productos/Listar/']);
        });
      }
    }catch(error) {
      console.error('Error al guardar producto:', error);
    }
  }
}
