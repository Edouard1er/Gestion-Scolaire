function readyPost() {
    $(document).ready(function () {
        $('#post-content').jqxTextArea({  placeHolder: "Publier une nouvelle poste", width: '100%', height: 80 });
        $("#post-submit-button").jqxButton({ width: 120, height: 40 });
        readPost();
    });
}

function createPost() {
    let posts_form = $('#posts-form');
    let data = posts_form.serialize();
    $.ajax( {
        type: "POST",
        url: "/post",
        data:data,
        success: function( response ) {
            $("#post-content").val("");
            readPost();
        },
        error: function( response ) {}						
    } );
}

function readPost(page=1) {
    $.ajax( {
        type: "GET",
        url: "/post/"+page,
        success: function( response ) {
            if(response && Object.keys(response).length > 0 && response.posts){
                populatePosts(response.posts)
                populatePagination(response.total,response.current,response.from,response.to,response.pages)
            }
        },
        error: function( response ) {}						
    } );
}

function updatePost(postId) {
    let content=$("#update-post-content").val()
    let data=`postId=${postId}&post=${content}`
    
    $.ajax( {
        type: "PUT",
        url: "/post",
        data:data,
        success: function( response ) {
            document.getElementById(`each-post-content-${postId}`).innerText=content
            $('#update-post-popover').jqxPopover('close');
            $(".update-post-wrapper").css("display","none"); 
        },
        error: function( response ) {}						
    } );
}

function deletePost(postId) {
    if(confirm("Voulez-vous vraiment supprimer la poste ?")){
        let data=`postId=${postId}`
        $.ajax( {
            type: "DELETE",
            url: "/post",
            data:data,
            success: function( response ) {
                readPost();
            },
            error: function( response ) {}						
        } );
    }
}

function populatePosts(posts) {
    $(document).ready(function () {
        if(posts && posts.length > 0){
            if($("#all-posts")){
                let html_div_post=``;
                posts.forEach(post => {
                    const datetime = toDateAndTime(post.created_at)
                    html_div_post += 
                    `<div id="post-${post.postId}" class="each-post">
                        <div> 
                            ${post.name && post.name.length > 0 ?post.name:post.username}  a ecrit 
                            ${datetime[1] && datetime[0].length > 0? ("le "+datetime[0]):"" } 
                            ${datetime[1] && datetime[1].length > 0? ("a "+datetime[1]):"" }
                        </div>
                        <div class="each-post-content">
                            <div id="each-post-content-${post.postId}">${post.contenu}</div>`;
                            if(post.editable==1){
                                html_div_post += 
                                `<div class="post-action">
                                    <button id="update-post-${post.postId}" title="Modifier la poste"  class="button-image" type="button" onclick="preparedUpdatePost(${post.postId})"><img alt="Modifier"  src="./img/editer.png"/></button>
                                    <button title="Supprimer la poste" class="button-image" type="button" onclick="deletePost(${post.postId})"><img alt="Supprimer" src="./img/delete.png"/></button>
                                </div>`
                            }
                        html_div_post +=    
                        `</div>
                    </div>`;
                    
                    $(document).ready(function () {
                        $("#post-"+post.postId).jqxExpander({ width: '100%', height: '50%'});
                    })
                })
                $("#all-posts").html(html_div_post)
            }
        }
    })
}

function populatePagination(total,current,from,to,pages) {
    if($("#pagination-post")){
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
        $("#pagination-post").html(html_pagination_post)
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

function preparedUpdatePost(postId) {
    let content = document.getElementById(`each-post-content-${postId}`).innerText
    $(".update-post-wrapper").css("display","block");
    $("#update-post-popover").jqxPopover({ offset: { left: 0, top: 0 }, isModal: true, 
        arrowOffsetValue: 0, position: "right", title: "Modification de poste", 
        showCloseButton: true, selector: $("#update-post-"+postId), width:"30%", height:"30%" });

    $('#update-post-content').jqxTextArea({  placeHolder: "Modifier votre poste", width: '100%', height: 80 });
    $('#update-post-content').val(content)
    $("#confirm-update-post-button").jqxButton({ width: 120, height: 40,template: "success" });
    $('#confirm-update-post-button').off('click');
    $('#confirm-update-post-button').on('click', function () { 
        updatePost(postId)
    })
    $("#cancel-update-post-button").jqxButton({ width: 120, height: 40,template: "info" });
    $('#cancel-update-post-button').off('click');
    $('#cancel-update-post-button').on('click', function () { 
        $('#update-post-content').val("")
        $('#update-post-popover').jqxPopover('close');
        $(".update-post-wrapper").css("display","none");
    })
    $('#update-post-popover').jqxPopover('open');
}