function react(id, type) {
  fetch('econtent_actions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=react&id=${id}&type=${type}`
  }).then(() => {
    const post = document.querySelector(`.post[data-id="${id}"]`);
    const likeIcon = post.querySelector('.fa-heart');
    const dislikeIcon = post.querySelector('.fa-thumbs-down');

    if (type === 'like') {
      likeIcon.classList.add('active');
      dislikeIcon.classList.remove('active');
    } else {
      dislikeIcon.classList.add('active');
      likeIcon.classList.remove('active');
    }
  });
}

function addComment(e, id) {
  if (e.key === 'Enter' && e.target.value.trim() !== '') {
    fetch('econtent_actions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=comment&id=${id}&text=${encodeURIComponent(e.target.value)}`
    }).then(() => {
      const box = document.getElementById(`comments-${id}`);
      const div = document.createElement('div');
      div.className = 'comment';
      div.innerHTML = `<b>You:</b> ${e.target.value}`;
      box.insertBefore(div, box.children[0]);
      e.target.value = '';
    });
  }
}

function download(id, path) {
  fetch('econtent_actions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=download&id=${id}`
  });
  window.location.href = path;
}

function share(id) {
  navigator.clipboard.writeText(location.origin + '/econtent.php#' + id);
  alert('Link copied');
}

/* DOUBLE TAP LIKE */
function likePost(id) {
  const post = document.querySelector(`.post[data-id="${id}"]`);
  const wrapper = post.querySelector('.media-wrapper');
  wrapper.classList.add('liked');
  react(id, 'like');
  setTimeout(() => wrapper.classList.remove('liked'), 600);
}
