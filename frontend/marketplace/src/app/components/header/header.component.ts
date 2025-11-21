import { Component,  HostListener, Inject, OnInit, PLATFORM_ID  } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NavigationEnd, Router } from '@angular/router';
import Swal from 'sweetalert2';
import { isPlatformBrowser } from '@angular/common';
import { filter } from 'rxjs/operators';
import { GlobalService } from '../../service/global-service.service';

@Component({
  selector: 'app-header',
  imports: [CommonModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss'
})
export class HeaderComponent implements OnInit {
  isNavbarActive = false;
  home = true;
  activo = false;
  idUser = '';
  productos: any[] = [];

  constructor(
    private router: Router,
    @Inject(PLATFORM_ID) private platformId: any,
    private global: GlobalService
  ) {}

  async ngOnInit() {
    if (this.router.url === '/'){
      this.home = true;
    }else{
      this.home = false;
    }
    if (isPlatformBrowser(this.platformId)) {
      this.Logeando();
      this.router.events
        .pipe(filter(event => event instanceof NavigationEnd))
        .subscribe((event: NavigationEnd) => {
          this.home = event.urlAfterRedirects === '/';
          this.Logeando();
          this.desactivar();
        });
    }
  }

  Logeando(){
    const idUser = localStorage.getItem('idUserFrontend');
    const nameUser = localStorage.getItem('userFrontend');
    if (idUser && nameUser){
      this.idUser = idUser;
      this.global.idUsuarioFrontend = idUser;
      this.global.nombreUsuarioFrontend = nameUser;
      this.activo = true;
      this.global.cargarDesdeLocalStorage();
      this.global.producto$.subscribe(data => {
        this.productos = data;
      });
    }
  }


  @HostListener('window:scroll', [])
  onWindowScroll() {
    const header: any = document.querySelector("[data-header]");
    const backTopBtn: any = document.querySelector("[data-back-top-btn]");
    if (window.scrollY > 100) {
      header.classList.add("active");
      backTopBtn.classList.add("active");
    } else {
      header.classList.remove("active");
      backTopBtn.classList.remove("active");
    }
  }

  toggleNavbar() {
    const navbar = document.querySelector('[data-navbar]');
    if (navbar) {
      this.isNavbarActive = !this.isNavbarActive;
      navbar.classList.toggle('active', this.isNavbarActive);
    }
  }

  desactivar(){
    const navbar = document.querySelector('[data-navbar]');
    if (navbar) {
      const tieneClase = navbar.classList.contains('active');
      if (tieneClase) {
        this.isNavbarActive = !this.isNavbarActive;
        navbar.classList.toggle('active', this.isNavbarActive);
      }
    }
  }

  buscar(event: any) {
    const input = event.target as HTMLInputElement;
    const termino = input.value.trim();
    console.log("Buscando:", termino);

    this.router.navigate(['/productos'], {
      queryParams: { search: termino }
    }).then(() => {
      window.location.reload();  // 🔥 fuerza el reload completo
    });
  }
  cerrarSesion(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
    Swal.fire({
      title: '',
      text: '¿Desea cerrar la sesión actual?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#0071ea',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        localStorage.clear();
        sessionStorage.clear();
        this.idUser = '';
        this.global.idUsuarioFrontend = '';
        this.global.limpiarLista();
        this.global.nombreUsuarioFrontend = '';
        this.activo = false;
        this.router.navigate(['/login'], { replaceUrl: true });
      }
    });
  }
}
