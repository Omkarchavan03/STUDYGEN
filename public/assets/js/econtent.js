/* ===================== LOAD SAVED THEME ===================== */
document.addEventListener("DOMContentLoaded", () => {

    if(localStorage.getItem("theme") === "true"){
        document.body.classList.add("dark");
    }

});


/* ===================== CENTRAL AJAX ===================== */
function post(data){
    return fetch("econtent.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body:new URLSearchParams(data)
    }).then(r=>r.json());
}



/* ===================== FOLLOW BUTTON ===================== */
document.addEventListener("click", e=>{

    const btn = e.target.closest(".follow-btn");
    if(!btn) return;

    const channelId = btn.dataset.channel;

    post({
        action:"follow",
        channel_id:channelId
    }).then(res=>{

        if(res.status === "success"){
            btn.innerText = res.following ? "Following" : "Follow";
            btn.classList.toggle("following", res.following);
        }

    });

});



/* ===================== POST ACTIONS ===================== */
document.addEventListener("click", e=>{

    const el = e.target.closest(".post-actions i");
    if(!el) return;

    const postEl = el.closest(".post");
    if(!postEl) return;

    const id = postEl.dataset.id;

    if(el.classList.contains("fa-thumbs-up")){
        react(id,"like",postEl);
    }

    else if(el.classList.contains("fa-thumbs-down")){
        react(id,"dislike",postEl);
    }

    else if(el.classList.contains("fa-download")){
        downloadPost(id,postEl);
    }

    else if(el.classList.contains("fa-share")){
        sharePost(id);
    }

    else if(el.classList.contains("fa-comment-dots")){
        toggleComments(id);
    }

});



/* ===================== POST LIKE / DISLIKE ===================== */
function react(id,type,postEl){

    post({
        action:"react",
        id:id,
        type:type
    }).then(res=>{

        if(res.status === "success"){

            const spans = postEl.querySelectorAll(".post-actions span");

            if(spans[0]) spans[0].innerText = res.likes;
            if(spans[1]) spans[1].innerText = res.dislikes;

        }

    });

}



/* ===================== ADD COMMENT ===================== */
function addComment(postId,text,input){

    text = text.trim();
    if(!text) return;

    post({
        action:"comment",
        id:postId,
        text:text
    }).then(res=>{

        if(res.status === "success"){

            const cSec = document.getElementById("comments-"+postId);
            if(!cSec) return;

            const div = document.createElement("div");

            div.className = "comment";
            div.id = "comment-"+res.comment_id;

            div.innerHTML = `
                <b>${res.comment.username}:</b> ${res.comment.comment}

                <button class="delete-comment-btn" data-id="${res.comment_id}">
                Delete
                </button>

                <button class="comment-like-btn" data-id="${res.comment_id}">
                👍 <span id="commentLike-${res.comment_id}">0</span>
                </button>

                <button class="comment-dislike-btn" data-id="${res.comment_id}">
                👎 <span id="commentDislike-${res.comment_id}">0</span>
                </button>
            `;

            cSec.appendChild(div);

            input.value="";

        }

    });

}



/* ===================== ENTER COMMENT ===================== */
document.addEventListener("keydown", e=>{

    if(e.target.matches(".comment-form input") && e.key === "Enter"){

        e.preventDefault();

        const postEl = e.target.closest(".post");
        if(!postEl) return;

        const id = postEl.dataset.id;

        addComment(id, e.target.value, e.target);

    }

});



/* ===================== DELETE COMMENT ===================== */
document.addEventListener("click", e=>{

    const btn = e.target.closest(".delete-comment-btn");
    if(!btn) return;

    const id = btn.dataset.id;

    if(!confirm("Delete comment?")) return;

    post({
        action:"delete_comment",
        comment_id:id
    }).then(res=>{

        if(res.status === "success"){

            const el = document.getElementById("comment-"+id);
            if(el) el.remove();

        }

    });

});



/* ===================== COMMENT LIKE / DISLIKE ===================== */
document.addEventListener("click", e=>{

    const like = e.target.closest(".comment-like-btn");
    const dislike = e.target.closest(".comment-dislike-btn");

    if(like){
        reactComment(like.dataset.id,"like");
    }

    if(dislike){
        reactComment(dislike.dataset.id,"dislike");
    }

});


function reactComment(id,type){

    post({
        action:"comment_react",
        comment_id:id,
        reaction:type
    }).then(res=>{

        if(res.status === "success"){

            const likeEl = document.getElementById("commentLike-"+id);
            const dislikeEl = document.getElementById("commentDislike-"+id);

            if(likeEl) likeEl.innerText = res.likes;
            if(dislikeEl) dislikeEl.innerText = res.dislikes;

        }

    });

}



/* ===================== TOGGLE COMMENTS ===================== */
function toggleComments(id){

    const el = document.getElementById("comments-"+id);
    if(!el) return;

    el.style.display = el.style.display === "none" ? "block" : "none";

}



/* ===================== DOWNLOAD ===================== */
function downloadPost(id,postEl){

    const link = postEl.querySelector(".post-content img, iframe, .post-file a");
    if(!link) return;

    const path = link.tagName === "A" ? link.href : link.src;

    post({
        action:"download",
        id:id
    });

    const a = document.createElement("a");

    a.href = path;
    a.download = path.split("/").pop();

    document.body.appendChild(a);
    a.click();
    a.remove();

}



/* ===================== SHARE ===================== */
function sharePost(id){

    const url = window.location.origin + window.location.pathname + "#post-"+id;

    navigator.clipboard.writeText(url)
    .then(()=> alert("Post link copied"));

}



/* ===================== THEME TOGGLE ===================== */
const toggleThemeBtn = document.getElementById("toggleTheme");

  // Apply saved theme early
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark-theme");
  }

  if (toggleThemeBtn) {
    const icon = toggleThemeBtn.querySelector("i");

    // Fix icon on load
    if (document.body.classList.contains("dark-theme")) {
      icon.classList.remove("fa-moon");
      icon.classList.add("fa-sun");
    }

    toggleThemeBtn.addEventListener("click", () => {
      document.body.classList.toggle("dark-theme");

      const isDark = document.body.classList.contains("dark-theme");

      icon.classList.toggle("fa-moon", !isDark);
      icon.classList.toggle("fa-sun", isDark);

      localStorage.setItem("theme", isDark ? "dark" : "light");
    });
  }

/* infinite scroll */

let loading = false;

window.addEventListener("scroll", () => {

    if(loading) return;

    if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 200){

        loadMore();

    }

});


function loadMore(){

const posts=document.querySelectorAll(".post");
const lastId=posts[posts.length-1].dataset.id;

const feed=document.getElementById("feed");
const type=feed.dataset.type;

fetch("scroll_feed.php?type="+type+"&last_id="+lastId)

.then(res=>res.text())

.then(data=>{

if(data.trim()!==""){
feed.insertAdjacentHTML("beforeend",data);
}

});

}