function readyPost() {
    $(document).ready(function () {
        $('#post-content').jqxTextArea({  placeHolder: "Publier une nouvelle poste", width: '30%', height: 80 });
        $("#post-submit-button").jqxButton({ width: 120, height: 40 });
        readPost();
    });
}
function createPost() {
    
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

function updatePost() {
    
}

function deletePost() {

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
                        <div>
                            ${post.contenu}
                        </div>
                    </div>`;
                    $(document).ready(function () {
                        $("#post-"+post.postId).jqxExpander({ width: '50%', height: '50%'});
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