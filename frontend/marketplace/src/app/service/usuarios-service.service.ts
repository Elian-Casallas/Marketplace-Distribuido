import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class UsuariosService {

  private apiUrl = environment.mainUrl;
  private headers = {
    'X-Internal-Key': environment.keyMain,
    'Accept': 'application/json'
  };

  constructor(private http: HttpClient) {}


  async Get(id: any, bandera: boolean) {
    try {
      // 1️⃣ Buscar si el usuario ya existe
      const existingUser: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/usuarios/${id}`, {
          headers: this.headers,
          params: {
            tipo: bandera ? 'identificacion' : 'id'
          }
        }),
      );
      console.log('Usuario encontrado:', existingUser);
      if ('id' in existingUser){
        const {name = '', email = '', identificacion = '', isSeller = false, telefono = '', domicilio = {}} = existingUser;
        return {
          name,
          email,
          identificacion,
          isSeller,
          telefono,
          domicilio
        };
      }else{
        return {existe: false};
      }
    } catch (error) {
      console.error('Error en el Get de usuarios:', error);
      throw error;
    }
  }

  async Logout(user: any) {
    try {
      console.log('Intentando login con usuario:', user);
      const data = {
        identificacion: user.iden,
        password: user.password
      };
      let existingUser : any;
      try{
        existingUser = await firstValueFrom(
          this.http.post(`${this.apiUrl}/usuarios/login`, data, { headers: this.headers } )
        );
      }catch{existingUser = {}; }
      // 2️⃣ Si existe, devolver el usuario encontrado
      if ('identificacion' in existingUser) {
        const name = existingUser.name || 'Sin nombre';
        return {existe: true, id: existingUser.id, name: name};
      }
      return {existe: false};
    } catch (error) {
      console.error('Error en Logout:', error);
      throw error;
    }
  }

  async Registro(user: any) {
    try {
      const userData = {
        "name": user.name,
        "email": user.email,
        "password": user.pass,
        "identificacion": user.iden,
        "isSeller": false,
        "telefono": "",
        "productosVenta": [],
        "domicilio": [],
      }
      const agregarUsuario: any = await firstValueFrom(
        this.http.post(`${this.apiUrl}/usuarios`, userData, { headers: this.headers } )
      )
      if ('error' in agregarUsuario){
        return {exito: false};
      }
      const {usuario} = agregarUsuario;
      return {exito: true, id: usuario.id};
    } catch (error) {
      console.error('Error en registro:', error);
      throw error;
    }
  }

  async Actualizar(user: any){
    try {
      let userData = {}
      if ('password' in user){
        if (user.password === ''){
          userData = {
            "name": user.name,
            "email": user.email,
            "telefono": user.telefono,
            "isSeller": user.isSeller,
          }
        }else{
          userData = {
            "name": user.name,
            "email": user.email,
            "password": user.password,
            "telefono": user.telefono,
            "isSeller": user.isSeller,
          }
        }
      }else{
        userData = {
          'domicilio': user.domicilio
        }
      }
      console.log('Datos para actualizar usuario:', userData);
      const actualizarUsuario: any = await firstValueFrom(
        this.http.put(`${this.apiUrl}/usuarios/${user.id}`, userData, { headers: this.headers } )
      )
      if ('error' in actualizarUsuario){
        return {exito: false};
      }
      const {usuario} = actualizarUsuario;
      return {exito: true, id: usuario.id};
    } catch (error) {
      console.error('Error en ActualizarEmpleado:', error);
      throw error;
    }
  }
}
