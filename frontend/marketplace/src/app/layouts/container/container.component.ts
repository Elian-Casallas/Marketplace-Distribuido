import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { FooterComponent } from "../../components/footer/footer.component";
import { HeaderComponent } from "../../components/header/header.component";

@Component({
  selector: 'app-container',
  imports: [RouterModule, FooterComponent, HeaderComponent],
  templateUrl: './container.component.html',
  styleUrl: './container.component.scss'
})
export class ContainerComponent {

}
