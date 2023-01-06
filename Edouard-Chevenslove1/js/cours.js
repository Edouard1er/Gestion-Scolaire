var userInfo=JSON.parse(localStorage.getItem("user"))

function readyClasses() {
    $(document).ready(function () {
        if(userInfo.role=="ELEVE"){
            $("#subscribed-button").jqxButton({ width: 120, height: 40 });
            $("#subscribed-button").on("click", (e)=>{
                subcribeClasses();
            })
        }else{
            $('#class-nom').jqxTextArea({  placeHolder: "Nom", width: '50%', height: 80 });
            $('#class-description').jqxTextArea({  placeHolder: "Description", width: '50%', height: 80 });
            $("#class-submit-button").jqxButton({ width: 120, height: 40 });
            $("#class-submit-button").on("click", (e)=>{
                createClass();
            })
        }
        readMyClasses();
        readOtherClasses();
    });
}

function readyEleve() {
    $(document).ready(function () {
        $("#note").jqxNumberInput({ width: '250px', height: '25px', spinButtons: true, max:20,groupSize:2, digits:2, min:0 });
        $("#note-button").jqxButton({ width: 120, height: 40 });
        $("#note-button").on("click", (e)=>{
            giveNote();
        })
        readMyEleve();
    });
}

function createClass() {
    let nom=$("#class-nom").val()
    let description=$("#class-description").val()
    let data={
        nom:nom,
        description:description
    }
    $.ajax( {
        type: "POST",
        url: "/classes",
        data:data,
        success: function( response ) {
            $("#class-nom").val("");
            $("#class-description").val("")
            readMyClasses();
        },
        error: function( response ) {}						
    } );
}

function readMyClasses(page=1) {
    if(userInfo.role=="ELEVE"){
        $.ajax( {
            type: "GET",
            url: "/classes/"+page+"/SUIVI",
            success: function( response ) {
                if(response && Object.keys(response).length > 0 && response.cours){
                    populateMyClasses(response.cours)
                    let moy=calculateAverage(response.cours)
                    if(moy){
                        $("#moyenne").text(moy)
                    }
                    // populatePagination(response.total,response.current,response.from,response.to,response.pages)
                }
            },
            error: function( response ) {}						
        } );
    }else{
        $.ajax( {
            type: "GET",
            url: "/classes/"+page+"/PROF",
            success: function( response ) {
                if(response && Object.keys(response).length > 0 && response.cours){
                    console.log("cours", response)
                    populateMyClasses(response.cours)
                    // populatePagination(response.total,response.current,response.from,response.to,response.pages)
                }
            },
            error: function( response ) {}						
        } );
    } 
}

function readMyEleve(page=1) {
    $.ajax( {
        type: "GET",
        url: "/classes/"+page+"/ELEVE",
        success: function( response ) {
            if(response && Object.keys(response).length > 0 && response.cours){
                console.log("cours", response)
                populateMyEleve(response.cours)
                // populatePagination(response.total,response.current,response.from,response.to,response.pages)
            }
        },
        error: function( response ) {}						
    } );
}

function calculateAverage(arr) {
    console.log(arr)
    var result = arr.reduce(function(acc, curr) {
      if (curr.note !== null) {
        curr.note=parseFloat(curr.note)
        acc.sum += curr.note;
        acc.count++;
      }
      return acc;
    }, {sum: 0, count: 0});
  
    // Calcul de la moyenne
    var average = result.sum / result.count;
  
    // Retour de la moyenne
    return average;
  }
  
  
  

function readOtherClasses(page=1) {
    if(userInfo.role=="ELEVE"){
        $.ajax( {
            type: "GET",
            url: "/classes/"+page+"",
            success: function( response ) {
                if(response && Object.keys(response).length > 0 && response.cours){
                    
                    populateOtherClasses(response.cours)
                    // populatePagination(response.total,response.current,response.from,response.to,response.pages)
                }
            },
            error: function( response ) {}						
        } );
    }
}



function updateClass(coursId) {
    let nom=$("#update-class-nom").val()
    let description=$("#update-class-description").val()
    let data={
        coursId:coursId,
        nom:nom,
        description:description
    }
    
    
    $.ajax( {
        type: "PUT",
        url: "/classes",
        data: data,
        success: function( response ) {
            $('#update-class-popover').jqxPopover('close');
            $(".update-class-wrapper").css("display","none"); 
            readMyClasses();
        },
        error: function( response ) {}						
    } );
}

function deleteClass(coursId) {
    if(confirm("Voulez-vous vraiment supprimer le cours ?")){
        let data=`coursId=${coursId}`
        $.ajax( {
            type: "DELETE",
            url: "/classes",
            data:data,
            success: function( response ) {
                readMyClasses();
            },
            error: function( response ) {}						
        } );
    }
}

function populateMyClasses(myClasses) {
    $(document).ready(function () {
        if(myClasses && myClasses.length > 0){
            if($("#my_classes_list_tbody")){
                let tbody_my_class=``;
                myClasses.forEach(my_class => {
                    const datetime = toDateAndTime(my_class.created_at, false)
                    if(userInfo.role=="ELEVE"){
                        tbody_my_class += 
                    `<tr id="${my_class.coursId}" class="each-my_class">
                        <td>${my_class.nom}</td>
                        <td>${my_class.description}</td>
                        <td>${my_class.professeur_nom?my_class.professeur_nom:my_class.professeur_username}</td>
                        <td>${my_class.note?my_class.note:""}</td>
                        <td>
                            <div class="my_class-action">
                                <button title="Quitter le cours" class="button-image" type="button" onclick="unSubscribedClass(${my_class.coursId})"><img alt="Quitter" src="./img/delete.png"/></button>
                            </div>
                        </td>
                    </tr>`;
                    }else{
                        tbody_my_class += 
                    `<tr id="${my_class.coursId}" class="each-my_class">
                        <td>${my_class.nom}</td>
                        <td>${my_class.description}</td>
                        <td>
                            <div class="my_class-action">
                                <button id="update-class-${my_class.coursId}" title="Modifier le cours"  class="button-image" type="button" onclick="preparedUpdateClass(${my_class.coursId},'${my_class.nom}','${my_class.description}')" ><img alt="Modifier"  src="./img/editer.png"/></button>
                                <button title="Supprimer le cours" class="button-image" type="button" onclick="deleteClass(${my_class.coursId})"><img alt="Supprimer" src="./img/delete.png"/></button>
                            </div>
                        </td>
                    </tr>`;
                   
                    }
                })
                $("#my_classes_list_tbody").html(tbody_my_class)
            }
        }
    })
}

function populateMyEleve(myClasses) {
    $(document).ready(function () {
        if(myClasses && myClasses.length > 0){
            if($("#my_classes_list_tbody")){
                let tbody_my_class=``;
                myClasses.forEach(my_class => {
                   
                        tbody_my_class += 
                    `<tr id="${my_class.userCoursId}" class="each-my_class">
                        <td>${my_class.nom}</td>
                        <td>${my_class.etudiant_name?my_class.etudiant_name:my_class.etudiant_username}</td>
                        <td>${my_class.note?my_class.note:""}</td>
                        <td>
                            <div class="my_class-action">
                            <input class="give_note" name="give_note" type="checkbox" class="button-image" type="button" value="${my_class.userCoursId}" />
                            </div>
                        </td>
                    </tr>`;
                })
                $("#my_classes_list_tbody").html(tbody_my_class)
            }
        }
    })
}

function populateOtherClasses(myClasses) {
    $(document).ready(function () {
        if(myClasses && myClasses.length > 0){
            if($("#other_classes_list_tbody")){
                let tbody_other_class=``;
                myClasses.forEach(my_class => {
                    const datetime = toDateAndTime(my_class.created_at, false)
                    tbody_other_class += 
                    `<tr id="${my_class.coursId}" class="each-my_class">
                        <td>${my_class.nom}</td>
                        <td>${my_class.description}</td>
                        <td>${my_class.professeur_nom?my_class.professeur_nom:my_class.professeur_username}</td>
                        <td>
                            <div class="my_class-action">
                                <input class="checked_other_classes" name="checked_other_classes" type="checkbox" title="Quitter le cours" class="button-image" type="button" value="${my_class.coursId}" />
                            </div>
                        </td>
                    </tr>`;
                })
                $("#other_classes_list_tbody").html(tbody_other_class)
            }
        }
    })
}

function echapper(str){
    return str.replace(/['"]/g, function(c) {
        return '&#' + c.charCodeAt(0) + ';';
      });
}


function populatePagination(total,current,from,to,pages) {
    if($("#pagination-class")){
        total = parseInt(total)
        current = parseInt(current)
        to = parseInt(to)
        pages = parseInt(pages)
        let page = parseInt($("#page") && $("#page").val() ? $("#page").val():1)
        let html_pagination_post =
        `<nav aria-label="...">
            <ul class="pagination">
                <li disabled class="pagination-button page-item ${from==page?'no-previous':''}" onclick="changePagePost(${page -1},${pages})">
                <a class="page-link">Precedent</a>
                </li>`;
                for (let eachPage = 1; eachPage <= pages; eachPage++) {
                    html_pagination_post += `<li class="page-item page-link pagination-number ${page==eachPage?'current-page':''}">
                    <a  onclick="changePagePost(${eachPage},${pages})">${eachPage}</a>
                    </li>`;
                }
                html_pagination_post +=
                `<li class="page-item">
                <a class="pagination-button page-link ${(pages == page)?'no-next':''}" onclick="changePagePost(${page + 1},${pages})">Suivant</a>
                </li>
            </ul>
        </nav>`;
        $("#pagination-class").html(html_pagination_post)
    }
    
}

function changePagePost(page=1,to=1) {
    if($("#page")){
        if(page > 0 && page <= to){
            $("#page").val(page);
            readPost(page);
        }
    }
}

function preparedUpdateClass(coursId, nom="", description="") {
    console.log(coursId,nom, description)
    $(".update-class-wrapper").css("display","block");
    $("#update-class-popover").jqxPopover({ offset: { left: 0, top: 0 }, isModal: true, 
        arrowOffsetValue: 0, position: "left", title: "Modification de cours", 
        showCloseButton: true, selector: $("#update-class-"+coursId), width:"30%", height:"40%" });

    $('#update-class-nom').jqxTextArea({  placeHolder: "Nom", width: '100%', height: 80 });
    $('#update-class-nom').val(nom)
    $('#update-class-description').jqxTextArea({  placeHolder: "Description", width: '100%', height: 80 });
    $('#update-class-description').val(description)
    $("#confirm-update-class-button").jqxButton({ width: 120, height: 40,template: "success" });
    $('#confirm-update-class-button').off('click');
    $('#confirm-update-class-button').on('click', function () { 
        updateClass(coursId)
    })
    $("#cancel-update-class-button").jqxButton({ width: 120, height: 40,template: "info" });
    $('#cancel-update-class-button').off('click');
    $('#cancel-update-class-button').on('click', function () { 
        $('#update-class-nom').val("")
        $('#update-class-description').val("")
        $('#update-class-popover').jqxPopover('close');
        $(".update-class-wrapper").css("display","none");
    })
    $('#update-class-popover').jqxPopover('open');
}

function subcribeClasses(){
    let checkedClasses = $('.checked_other_classes:checked').map(function() {
        return this.value;
    }).get();
      
    if(checkedClasses.length > 0){

        $.ajax( {
        type: "POST",
        url: "/classes/subscribe",
        data:{"list_cours":JSON.stringify(checkedClasses)},
        success: function( response ) {
            readMyClasses();
            readOtherClasses();
            window.location.reload()
        },
        error: function( response ) {}						
        } );
    }else{
        alert("Cocher au moins un cours")
    }
}

function giveNote(){
    let checkedClasses = $('.give_note:checked').map(function() {
        return this.value;
    }).get();
    let note = $("#note").val()
    if(parseFloat(note)>0){
        if(checkedClasses.length > 0){

            $.ajax( {
            type: "POST",
            url: "/classes/note",
            data:{"list_cours":JSON.stringify(checkedClasses), "note":note},
            success: function( response ) {
                readMyEleve();
            },
            error: function( response ) {}						
            } );
        }else{
            alert("Cocher au moins un eleve")
        }
    }else{
        alert("Donner une note superieure a 0")
    }
    
}

function unSubscribedClass (coursId) { 
    
    if(coursId && confirm("Voulez-vous vraiment quitter le cours ?\nTu perdras tes notes pour ce cours.")){
        $.ajax( {
            type: "DELETE",
            url: "/classes/subscribe",
            data:{"coursId":coursId},
            success: function( response ) {
                readMyClasses();
                readOtherClasses();
            },
            error: function( response ) {}						
            } );
    }
}