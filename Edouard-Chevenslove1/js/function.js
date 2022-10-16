function checkFieldValidity(id,message="") {
    if(message.length ==0){
        message="Veuillez remplir ce champs."
    }    
    document.getElementById(id).addEventListener("invalid", function(e) {
        e.target.setCustomValidity(message);
    });
}
