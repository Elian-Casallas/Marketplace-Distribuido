import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { sesionGuard } from './sesion.guard';

describe('sesionGuard', () => {
  const executeGuard: CanActivateFn = (...guardParameters) => 
      TestBed.runInInjectionContext(() => sesionGuard(...guardParameters));

  beforeEach(() => {
    TestBed.configureTestingModule({});
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });
});
