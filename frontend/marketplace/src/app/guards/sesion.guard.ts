import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import Swal from 'sweetalert2';

export const sesionGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const user = localStorage.getItem('userFrontend');

  if (!user) {
    router.navigate(['/login']);
    Swal.fire({
      icon: 'warning',
      title: '',
      html: 'Para entrar necesitas una cuenta, por favor crea o entra en sesión en una cuenta para continuar',
      allowOutsideClick: false,
      confirmButtonText: 'Ok',
    })
    return false;
  }

  return true;
};
