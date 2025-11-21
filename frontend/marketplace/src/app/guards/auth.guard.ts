import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import Swal from 'sweetalert2';

export const authGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const user = localStorage.getItem('userFrontend');

  if (user) {
    router.navigate(['/']);
    Swal.fire({
      icon: 'warning',
      title: '',
      html: 'Ya estás logeado, por favor cierra sesión para continuar',
      allowOutsideClick: false,
      confirmButtonText: 'Ok',
    })
    return false;
  }

  return true;
};
