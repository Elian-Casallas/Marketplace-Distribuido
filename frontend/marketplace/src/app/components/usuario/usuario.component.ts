import { Component, Inject, OnInit, PLATFORM_ID, ViewChild, ElementRef  } from '@angular/core';
import { Router, ActivatedRoute, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { CommonModule } from '@angular/common';
import { isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import Swal from 'sweetalert2';
import { UsuariosService } from '../../service/usuarios-service.service';

@Component({
  selector: 'app-usuario',
  imports: [FormsModule, CommonModule],
  templateUrl: './usuario.component.html',
  styleUrl: './usuario.component.scss'
})
export class UsuarioComponent implements OnInit {

  idUser: any;
  nombre: string | any = '';
  email: string | any = '';
  iden: string | any = '';
  pass: string | any = '';
  telefono: string | any = '';
  disabledt = false;
  //
  isSeller: boolean | any = false;
  vendedorArray: any[] = [{
    text: "Vendedor", value: true
  }, {
    text: "Comprador", value: false
  }];
  //
  direccion: string | any = '';
  departamento: string | any = '';
  municipio: string | any = '';
  barrio: string | any = '';
  lugar: string | any = '';
  domicilio: string | any = 'Residencial';
  domicilioArray: any[] = ["Residencial", "Laboral"];

  @ViewChild('email') emailRef!: ElementRef;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    @Inject(PLATFORM_ID) private platformId: any,
    private usuarioService: UsuariosService,
  ) {
    this.route.params.subscribe(params => {
      this.idUser = params['id'];
    });
  }

  async ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      this.ejecutar();
      this.router.events
        .pipe(filter(event => event instanceof NavigationEnd))
        .subscribe((event: NavigationEnd) => {
          this.ejecutar();
        });
    }
  }

  async ejecutar(){
    const idUser = localStorage.getItem('idUserFrontend');
    if (idUser === this.idUser){
      window.scrollTo({ top: 0, behavior: 'smooth' });
      Swal.fire({
        title: "Buscando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      await this.InformacionUser();
      this.disabledt = false;
      Swal.fire({
        icon: 'success',
        title: '',
        html: 'Información encontrada exitosamente',
        allowOutsideClick: false,
        confirmButtonText: 'Ok',
      })
    }else{
      this.disabledt = true;
      this.router.navigate([`/usuario/${idUser}`]);
    }
  }

  async InformacionUser(){
    try{
      const response: any = await this.usuarioService.Get(this.idUser, false);
      if ('existe' in response){
        this.ErrorMensaje('Información');
        this.disabledt = true;
      }else{
        const {name, email, identificacion, isSeller, telefono, domicilio} = response;
        this.nombre = name;
        this.emailRef.nativeElement.value = email;
        this.email = email;
        this.iden = identificacion;
        this.isSeller = isSeller;
        this.telefono = telefono;
        const {direccion, departamento, municipio, barrio, lugar, tipo} = domicilio;
        this.direccion = direccion;
        this.departamento = departamento;
        this.municipio = municipio;
        this.barrio = barrio;
        this.lugar = lugar;
        this.domicilio = tipo;
      }
    }catch{
      this.ErrorMensaje('Información');
    }
  }

  ErrorMensaje(mensaje: string) {
    Swal.fire({
      icon: 'error',
      title: '¡Error!',
      html:  `${mensaje} no encontrada.`,
      allowOutsideClick: false,
      confirmButtonText: 'Ok',
    })
  }

  OnSelectEstado(){
    if (typeof this.isSeller === 'string'){
      this.isSeller = this.isSeller === 'true';
    }
  }

  async actualizar(){
    try{
      Swal.fire({
        title: "Actualizando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      const userData = {id: this.idUser, name: this.nombre, email: this.emailRef.nativeElement.value, password: this.pass, isSeller: this.isSeller, telefono: this.telefono};
      const response: any = await this.usuarioService.Actualizar(userData);
      if (response){
        Swal.fire('', 'El usuario a sido actualizado exitosamente.', 'success');
      }
    }catch(error) {
      console.error('Error al a actualizar el usuario:', error);
    }
  }

  async actualizarDomicilio(){
    try{
      Swal.fire({
        title: "Actualizando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      const userData = {id: this.idUser, domicilio: {direccion: this.direccion, departamento: this.departamento, municipio: this.municipio, barrio: this.barrio, lugar: this.lugar, tipo: this.domicilio}};
      const response: any = await this.usuarioService.Actualizar(userData);
      if (response){
        Swal.fire('', 'El domicilio a sido actualizado exitosamente.', 'success');
      }
    }catch(error) {
      console.error('Error al a actualizar el usuario:', error);
    }
  }
}
