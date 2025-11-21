import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Html404Component } from './html404.component';

describe('Html404Component', () => {
  let component: Html404Component;
  let fixture: ComponentFixture<Html404Component>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Html404Component]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Html404Component);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
