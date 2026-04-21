function validar(){

let nombre = document.getElementById("nombre");
let correo = document.getElementById("correo");
let mensaje = document.getElementById("mensaje");

let error = "";

if(nombre.value == ""){
error += "Introduce el nombre\n";
}

if(correo.value == ""){
error += "Introduce el correo\n";
}

if(mensaje.value == ""){
error += "Introduce el mensaje\n";
}

if(error != ""){
alert(error);
return false;
}

alert("Formulario enviado correctamente");
return true;

}

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("contacto-form");
  if (!form) return;

  form.addEventListener("submit", (event) => {
    if (!validar()) {
      event.preventDefault();
    }
  });
});


