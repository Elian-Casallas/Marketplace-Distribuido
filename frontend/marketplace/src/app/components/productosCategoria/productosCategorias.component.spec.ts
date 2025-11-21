import { ComponentFixture, TestBed } from '@angular/core/testing';

import { productosCategoriasComponent } from './productosCategorias.component';

describe('ProductosComponent', () => {
  let component: productosCategoriasComponent;
  let fixture: ComponentFixture<productosCategoriasComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [productosCategoriasComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(productosCategoriasComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
