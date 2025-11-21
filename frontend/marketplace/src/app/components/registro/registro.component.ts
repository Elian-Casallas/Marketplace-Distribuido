import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import Swal from 'sweetalert2';
import { UsuariosService } from '../../service/usuarios-service.service';

@Component({
  selector: 'app-registro',
  imports: [],
  templateUrl: './registro.component.html',
  styleUrl: './registro.component.scss'
})
export class RegistroComponent {
  constructor(
    private router: Router,
    private http: HttpClient,
    private usuarioService: UsuariosService,
  ) {}

  async onRegister(event: Event, name: string, email: string, iden: string, password: string) {
    event.preventDefault();
    try{
      if (name.trim() === '' || email.trim() === '' || iden.trim() === '' || password.trim() === '') return;
      Swal.fire({
        title: "Guardando...",
        text: 'Por favor, espera.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // 📌 Muestra animación de carga
        }
      });
      const userData = { name, email, iden, pass: password };
      const response: any = await this.usuarioService.Registro(userData);
      const {exito = false, id = 'sin registro'} = response;
      console.log(response)
      if (exito){
        localStorage.setItem('userFrontend', name);
        localStorage.setItem('idUserFrontend', id);
        Swal.fire('', 'El usuario a sido creado exitosamente.', 'success')
        .then(() => {this.router.navigate(['/']);});
      }else{
        Swal.fire('', 'En la base de datos ya hay un usuario registrado con está cedula: ' + iden, 'error');
      }
    }catch(error) {
      console.error('Error al logear usuario:', error);
    }
  }
}
