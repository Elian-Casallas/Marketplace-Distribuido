import { Routes } from '@angular/router';
import { ContainerComponent } from './layouts/container/container.component';
import { HomeComponent } from './components/home/home.component';
import { LoginComponent } from './components/login/login.component';
import { Html404Component } from './components/html404/html404.component';
import { RegistroComponent } from './components/registro/registro.component';
import { authGuard } from './guards/auth.guard';
import { CarritoComponent } from './components/carrito/carrito.component';
import { UsuarioComponent } from './components/usuario/usuario.component';
import { ProductosComponent } from './components/productos/productos.component';
import { ProductoComponent } from './components/producto/producto.component';
import { productosCategoriasComponent } from './components/productosCategoria/productosCategorias.component';
import { AgregarProductoComponent } from './components/agregar-producto/agregar-producto.component';
import { sesionGuard } from './guards/sesion.guard';
import { ListarProductosComponent } from './components/listar-productos/listar-productos.component';
import { EditarProductosComponent } from './components/editar-productos/editar-productos.component';
import { PrivacidadComponent } from './components/privacidad/privacidad.component';
import { ReembolsosComponent } from './components/reembolsos/reembolsos.component';
import { EnviosComponent } from './components/envios/envios.component';
import { CondicionesComponent } from './components/condiciones/condiciones.component';
import { SeguimientoComponent } from './components/seguimiento/seguimiento.component';
import { SobreNosotrosComponent } from './components/sobre-nosotros/sobre-nosotros.component';
import { ContactoComponent } from './components/contacto/contacto.component';
import { FaqComponent } from './components/faq/faq.component';
import { VendedoresComponent } from './components/vendedores/vendedores.component';

export const routes: Routes = [
  {
    path: '',
    component: ContainerComponent,
    children: [
      {
        path: '',
        component: HomeComponent
      },
      {
        canActivate: [authGuard],
        path: 'login',
        component: LoginComponent
      },
      {
        canActivate: [authGuard],
        path: 'registro',
        component: RegistroComponent
      },
      {
        canActivate: [sesionGuard],
        path: 'carrito',
        component: CarritoComponent
      },
      {
        canActivate: [sesionGuard],
        path: 'usuario/:id',
        component: UsuarioComponent
      },
      {
        path: 'productos',
        component: ProductosComponent
      },
      {
        path: 'productos/:category',
        component: productosCategoriasComponent
      },
      {
        path: 'producto/:category/:id',
        component: ProductoComponent
      },
      {
        canActivate: [sesionGuard],
        path: 'Ventas/Productos/Agregar',
        component: AgregarProductoComponent
      },
      {
        canActivate: [sesionGuard],
        path: 'Ventas/Productos/Listar',
        component: ListarProductosComponent
      },
      {
        canActivate: [sesionGuard],
        path: 'Ventas/Productos/editar/:id',
        component: EditarProductosComponent
      },
      {
        path: 'privacidad',
        component: PrivacidadComponent
      },
      {
        path: 'reembolsos',
        component: ReembolsosComponent
      },
      {
        path: 'envios',
        component: EnviosComponent
      },
      {
        path: 'condiciones',
        component: CondicionesComponent
      },
      {
        path: 'seguimiento',
        component: SeguimientoComponent
      },
      {
        path: 'sobre-nosotros',
        component: SobreNosotrosComponent
      },
      {
        path: 'contacto',
        component: ContactoComponent
      },
      {
        path: 'preguntas-frecuentes',
        component: FaqComponent
      },
      {
        path: 'vendedores',
        component: VendedoresComponent
      },
    ]
  },
  { path: '**', component: Html404Component },
];
