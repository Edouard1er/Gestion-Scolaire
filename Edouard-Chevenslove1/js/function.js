function checkFieldValidity(id,message="") {
    if(message.length ==0){
        message="Veuillez remplir ce champs."
    }    
    document.querySelector("#"+id).addEventListener("invalid", function(e) {
        e.target.setCustomValidity(message);
    });
}

// checkFieldValidity("email", "Veuillez entrer une adresse correcte")
// checkFieldValidity("password")