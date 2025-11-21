import { Component } from '@angular/core';
import { FooterComponent } from "../footer/footer.component";
import { HeaderComponent } from "../header/header.component";

@Component({
  selector: 'app-html404',
  imports: [FooterComponent, HeaderComponent],
  templateUrl: './html404.component.html',
  styleUrl: './html404.component.scss'
})
export class Html404Component {

}
