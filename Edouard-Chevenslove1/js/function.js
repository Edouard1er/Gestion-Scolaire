function checkFieldValidity(id,message="") {
    if(message.length ==0){
        message="Veuillez remplir ce champs."
    }    
    document.getElementById(id).addEventListener("invalid", function(e) {
        e.target.setCustomValidity(message);
    });
}

function toDateAndTime(datetime, toArray=true){
    let response=[];
    if(datetime){
        datetime= datetime.split(" ");
        response[0]=datetime[0].split("-").reverse().join("/");
        if(datetime[1] && datetime[1].length > 0){
            datetime= datetime[1].split(":");
            response[1]=datetime[0]+":"+datetime[1];
        }
        if(!toArray)
            return response.join(" ")
    }
    return response;
}