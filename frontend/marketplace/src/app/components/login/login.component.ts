import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import Swal from 'sweetalert2';
import { UsuariosService } from '../../service/usuarios-service.service';

@Component({
  selector: 'app-login',
  imports: [],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  constructor(
    private router: Router,
    private http: HttpClient,
    private usuarioService: UsuariosService
  ) {}

  async onLogin(event: Event, username: string, password: string) {
    event.preventDefault();
    try{
      if (username.trim() === '' || password.trim() === '') return;
      Swal.fire({
        title: "Buscando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      const userData = { iden: username, password: password };
      const response: any = await this.usuarioService.Logout(userData);
      const {existe=false, name} = response;
      if (!existe){
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          html: 'Usuario o contraseña incorrecto',
          allowOutsideClick: false,
          confirmButtonText: 'Ok',
        })
        return;
      }else{
        Swal.fire({
          icon: 'success',
          title: '',
          html: 'Bienvenido de nuevo ' + name,
          allowOutsideClick: false,
          confirmButtonText: 'Ok',
        })
      }
      console.log('Respuesta del servidor:', response);
      localStorage.setItem('userFrontend', name);
      localStorage.setItem('idUserFrontend', response.id);
      this.router.navigate(['/']);
    }catch(error) {
      console.error('Error al logear usuario:', error);
    }
  }
}
