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

function connecter(){
    let posts_form = $('#posts-login');
    let data = posts_form.serialize();
    $.ajax( {
        type: "POST",
        url: "/login",
        data:data,
        success: function( response ) {
            if(response && Object.keys(response).length > 0){
                if(response.code == 1){
                    localStorage.setItem("user",JSON.stringify(response.data))
                        window.open("/form.html","_self");
                } else {
                    document.getElementById("login-feedback").innerText=response.message
                    $("#login-feedback").show()
                }
                
            }
        },
        error: function( response ) {}						
    } );
}

function connectTest () {  
    $.ajax( {
        type: "POST",
        url: "/login",
        data:"request=isLogged",
        success: function( response ) {
            if(response && Object.keys(response).length > 0){
                if(response.code == 1){
                    localStorage.setItem("user",JSON.stringify(response.data))
                    console.log("user",localStorage.getItem("user"))
                    window.open("/form.html","_self");
                } 
            }
        },
        error: function( response ) {}						
    } );
}

function update_password_(){
    let posts_form = $('#update_password');
    let data = posts_form.serialize();
    $.ajax( {
        type: "POST",
        url: "/update_password",
        data:data,
        success: function( response ) {
            if(response && Object.keys(response).length > 0){
                if(response.code == 1){
                    let  userInfo=JSON.parse(localStorage.getItem("user"))
                    userInfo.first_login=0;
                    localStorage.setItem("user",JSON.stringify(userInfo))
                        alert("Mot de passe change avec success")
                        window.location.reload()
                }                 
            }
        },
        error: function( response ) {
            if(response.code=-2){
                alert("Il faut mettre un mot de passe different quand meme. lol")
            }
        }						
    } );
}

