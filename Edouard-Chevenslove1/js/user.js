function readyUser() {  
    $(document).ready(function () {  
        var roles ={
            "ELEVE":"Eleve",
            "PROF":"Enseignant",
            "ADMIN":"Administrateur"
        }
        $("#username").jqxInput({  width: '300px', height: '30px' });         
        $("#password").jqxPasswordInput({  width: '300px', height: '30px', showStrength: true, showStrengthPosition: "right" });
        $("#confirm_password").jqxPasswordInput({  width: '300px', height: '30px' });
        $("#role").jqxDropDownList({  source: roles, selectedIndex: -1, width: '300px', height: '30px', promptText: "C'est un...", autoDropDownHeight:true });
        $("#submit").jqxButton({ theme: theme });
        $("#submit").on("click", function() {
            createUser();
        })
        readUser();
    });
}


function createUser() {
    let users_form = $('#form_user');
    let data = users_form.serialize();
    $.ajax( {
        type: "POST",
        url: "/user",
        data:data,
        success: function( response ) {
            readUser();
            $("#form_user")[0].reset()
            $("#role").jqxDropDownList({selectedIndex: -1})
        },
        error: function( response ) {}						
    } );
}

function readUser() {
    $.ajax( {
        type: "GET",
        url: "/user/",
        success: function( response ) {
            if(response && Object.keys(response).length > 0 && response.data){
                populateUsers(response.data)
                // populatePagination(response.total,response.current,response.from,response.to,response.pages)
            }
        },
        error: function( response ) {}						
    } );
}

function updateUser(userId) {
    let username=$("#update_username").val()
    let role=$("#update_role").val()
    let data=`userId=${userId}&username=${username}&role=${role}`
    
    $.ajax( {
        type: "PUT",
        url: "/user",
        data:data,
        success: function( response ) {
            $('#update-user-popover').jqxPopover('close');
            $(".update-user-wrapper").css("display","none"); 
            readUser();
        },
        error: function( response ) {}						
    } );
}

function deleteUser(userId) {
    if(confirm("Voulez-vous vraiment supprimer l'utilisateur ?")){
        let data=`userId=${userId}`
        $.ajax( {
            type: "DELETE",
            url: "/user",
            data:data,
            success: function( response ) {
                readUser();
            },
            error: function( response ) {}						
        } );
    }
}

function populateUsers(users) {
    $(document).ready(function () {
        if(users && users.length > 0){
            if($("#user_list_tbody")){
                let tbody_user=``;
                users.forEach(user => {
                    const datetime = toDateAndTime(user.created_at, false)
                    tbody_user += 
                    `<tr id="${user.userId}" class="each-user">
                        <td>${datetime}</td>
                        <td>${user.username}</td>
                        <td>${user.role_libelle}</td>
                        <td>
                            <div class="user-action">
                                <button id="update-user-${user.userId}" title="Modifier l'utilisateur"  class="button-image" type="button" onclick="preparedUpdateUser(${user.userId},'${user.username}','${user.role_code}')"><img alt="Modifier"  src="./img/editer.png"/></button>
                                <button title="Supprimer l'utilisateur" class="button-image" type="button" onclick="deleteUser(${user.userId})"><img alt="Supprimer" src="./img/delete.png"/></button>
                            </div>
                        </td>
                    </tr>`;
                })
                $("#user_list_tbody").html(tbody_user)
            }
        }
    })
}

function populatePagination(total,current,from,to,pages) {
    if($("#pagination-user")){
        total = parseInt(total)
        current = parseInt(current)
        to = parseInt(to)
        pages = parseInt(pages)
        let page = parseInt($("#page") && $("#page").val() ? $("#page").val():1)
        let html_pagination_user =
        `<nav aria-label="...">
            <ul class="pagination">
                <li disabled class="pagination-button page-item ${from==page?'no-previous':''}" onclick="changePageUser(${page -1},${pages})">
                <a class="page-link">Precedent</a>
                </li>`;
                for (let eachPage = 1; eachPage <= pages; eachPage++) {
                    html_pagination_user += `<li class="page-item page-link pagination-number ${page==eachPage?'current-page':''}">
                    <a  onclick="changePageUser(${eachPage},${pages})">${eachPage}</a>
                    </li>`;
                }
                html_pagination_user +=
                `<li class="page-item">
                <a class="pagination-button page-link ${(pages == page)?'no-next':''}" onclick="changePageUser(${page + 1},${pages})">Suivant</a>
                </li>
            </ul>
        </nav>`;
        $("#pagination-user").html(html_pagination_user)
    }
    
}

function changePageUser(page=1,to=1) {
    if($("#page")){
        if(page > 0 && page <= to){
            $("#page").val(page);
            readUser(page);
        }
    }
}

function preparedUpdateUser(userId, username, role) {
    $(".update-wrapper").css("display","block");
    $("#update-user-popover").jqxPopover({ offset: { left: 0, top: 0 }, isModal: true, 
        arrowOffsetValue: 0, position: "right", title: "Modification de l'utilisateur", 
        showCloseButton: true, selector: $("#update-user-"+userId), width:"30%", height:"30%" });

        var roles ={
            "ELEVE":"Eleve",
            "PROF":"Enseignant",
            "ADMIN":"Administrateur"
        }
        $("#update_username").jqxInput({  width: '300px', height: '30px' });
        $("#update_username").val(username)         
        // $("#update_role").jqxDropDownList({  source: roles, selectedIndex: -1, width: '300px', height: '30px', promptText: "C'est un...", autoDropDownHeight:true });
        $("#update_role").val(role)

    $("#confirm-update-user-button").jqxButton({ width: 120, height: 40,template: "success" });
    $('#confirm-update-user-button').off('click');
    $('#confirm-update-user-button').on('click', function () { 
        updateUser(userId)
    })
    $("#cancel-update-user-button").jqxButton({ width: 120, height: 40,template: "info" });
    $('#cancel-update-user-button').off('click');
    $('#cancel-update-user-button').on('click', function () { 
        $('#update-user-content').val("")
        $('#update-user-popover').jqxPopover('close');
        $(".update-wrapper").css("display","none");
    })
    $('#update-user-popover').jqxPopover('open');
}