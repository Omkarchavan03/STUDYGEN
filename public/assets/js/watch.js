/* ========================================
   POST HELPER
======================================== */
function post(data){
  return fetch("",{
    method:"POST",
    headers:{"Content-Type":"application/x-www-form-urlencoded"},
    body:new URLSearchParams(data)
  }).then(r=>r.json());
}

/* ========================================
   DESCRIPTION TOGGLE
======================================== */
function toggleDesc(){
  const text = document.getElementById("descText");
  const btn = document.getElementById("descBtn");
  if(text.classList.contains("collapsed")){
    text.classList.remove("collapsed");
    btn.innerText = "Show less";
  }else{
    text.classList.add("collapsed");
    btn.innerText = "Show more";
  }
}

/* ========================================
   COMMENT SUBMIT
   Updates dynamically without reload
======================================== */
function commentVideo(){
  const input = document.getElementById("commentText");
  const text = input.value.trim();
  if(!text) return;

  post({action:"comment", comment:text}).then(res=>{
    if(res.status==="success"){
      input.value="";
      const c = res.comment;
      const container = document.getElementById("commentSection");

      const div = document.createElement("div");
      div.className = "comment-box";
      div.id = "comment-" + c.id;
      div.innerHTML = `
        <strong>${c.username}</strong>
        <p>${c.comment}</p>
        <button onclick="reactComment(${c.id},'like')">👍 <span id="commentLike-${c.id}">0</span></button>
        <button onclick="reactComment(${c.id},'dislike')">👎 <span id="commentDislike-${c.id}">0</span></button>
        <button onclick="deleteComment(${c.id})">Delete</button>
      `;
      container.prepend(div);
    }
  });
}

/* ========================================
   FOLLOW / UNFOLLOW CHANNEL
   Updates dynamically
======================================== */
function followChannel(btn){
  const channelId = btn.dataset.channel;
  post({action:"follow", channel_id:channelId}).then(res=>{
    if(res.status==="success"){
      if(res.following){
        btn.innerText = "Following";
        btn.classList.add("following");
      }else{
        btn.innerText = "Follow";
        btn.classList.remove("following");
      }
    }
  });
}

/* ========================================
   VIDEO LIKE / DISLIKE
   Updates dynamically
======================================== */
function react(type){
  post({action:"react", reaction:type}).then(res=>{
    if(res.status==="success"){
      document.getElementById("likeCount").innerText = res.likes;
      document.getElementById("dislikeCount").innerText = res.dislikes;
    }
  });
}

/* ========================================
   SAVE VIDEO
   Updates dynamically
======================================== */
function saveVideo(){
  post({action:"save"}).then(res=>{
    if(res.status==="success"){
      const text = document.getElementById("saveText");
      text.innerText = res.saved ? "Saved" : "Save";
    }
  });
}

/* ========================================
   SHARE VIDEO
======================================== */
function shareVideo(){
  if(navigator.clipboard){
    navigator.clipboard.writeText(window.location.href)
      .then(()=>alert("Video link copied"));
  }else{
    alert("Copy this link: "+window.location.href);
  }
}

/* ========================================
   COMMENT LIKE / DISLIKE
   Updates dynamically
======================================== */
function reactComment(id,type){
  post({action:"comment_react", comment_id:id, reaction:type}).then(res=>{
    if(res.status==="success"){
      document.getElementById("commentLike-"+id).innerText = res.likes;
      document.getElementById("commentDislike-"+id).innerText = res.dislikes;
    }
  });
}

/* ========================================
   DELETE COMMENT
   Removes element dynamically
======================================== */
function deleteComment(id){
  if(!confirm("Delete this comment?")) return;
  post({action:"delete_comment", comment_id:id}).then(res=>{
    if(res.status==="success"){
      const el = document.getElementById("comment-"+id);
      if(el) el.remove();
    }
  });
}

/* ========================================
   THEME TOGGLE
======================================== */
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