/**
 * Feed Page Logic
 */

// Check authentication
if (!API.isAuthenticated()) {
  window.location.href = "login.html";
}

const currentUser = API.getUser();
let currentPosts = [];

// Initialize feed
document.addEventListener("DOMContentLoaded", () => {
  loadFeed();
  setupEventListeners();
  updateNavbar();
});

// Update navbar with user info
function updateNavbar() {
  const userNameElement = document.getElementById("currentUserName");
  if (userNameElement) {
    userNameElement.textContent = currentUser.username;
  }
}

// Load feed posts
async function loadFeed() {
  const feedContainer = document.getElementById("feedContainer");
  const loadingElement = document.getElementById("loading");

  try {
    loadingElement?.classList.remove("hidden");

    const response = await API.getPosts();

    if (response.success && response.data.length > 0) {
      currentPosts = response.data;
      renderPosts(response.data);
    } else {
      feedContainer.innerHTML = `
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3>No posts yet</h3>
                    <p>Start sharing your moments!</p>
                </div>
            `;
    }
  } catch (error) {
    Utils.showToast("Failed to load feed", "error");
    console.error(error);
  } finally {
    loadingElement?.classList.add("hidden");
  }
}

// Render posts
function renderPosts(posts) {
  const feedContainer = document.getElementById("feedContainer");
  feedContainer.innerHTML = "";

  posts.forEach((post) => {
    const postElement = createPostElement(post);
    feedContainer.appendChild(postElement);
  });
}

// Create post element
function createPostElement(post) {
  const article = document.createElement("article");
  article.className = "post-card";
  article.dataset.postId = post.id;

  const isOwner = post.user_id === currentUser.id;

  // Fix avatar path - replace .jpg with .svg for default avatar
  const avatarPath = post.profile_picture
    ? "uploads/" +
      post.profile_picture.replace("default-avatar.jpg", "default-avatar.svg")
    : "uploads/default-avatar.svg";

  article.innerHTML = `
        <div class="post-header">
            <img src="http://localhost:8000/${avatarPath}" 
                 alt="${post.username}" 
                 class="avatar">
            <div class="post-user-info">
                <div class="post-username">${post.full_name}</div>
                <div class="post-time">${Utils.timeAgo(post.created_at)}</div>
            </div>
            ${
              isOwner
                ? `
                <button class="post-menu-btn" onclick="deletePost(${post.id})">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            `
                : ""
            }
        </div>

        ${
          post.image_url
            ? `
            <div class="post-image-container">
                <img src="http://localhost:8000/${post.image_url}" alt="Post image" class="post-image">
            </div>
        `
            : ""
        }

        <div class="post-actions">
            <button class="action-btn ${post.is_liked ? "liked" : ""}" onclick="toggleLike(${post.id})">
                <svg fill="${post.is_liked ? "currentColor" : "none"}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="likes-count">${post.likes_count}</span>
            </button>
            <button class="action-btn" onclick="focusComment(${post.id})">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>${post.comments_count}</span>
            </button>
        </div>

        ${
          post.caption
            ? `
            <div class="post-caption">
                <span class="username">${post.username}</span>
                ${post.caption}
            </div>
        `
            : ""
        }

        <div class="comments-section" id="comments-${post.id}" style="display: none;"></div>

        <div class="add-comment">
            <input type="text" 
                   class="comment-input" 
                   placeholder="Add a comment..." 
                   id="comment-input-${post.id}"
                   onkeypress="handleCommentKeyPress(event, ${post.id})">
            <button class="comment-submit" onclick="addComment(${post.id})">Post</button>
        </div>
    `;

  return article;
}

// Toggle like
async function toggleLike(postId) {
  try {
    const response = await API.toggleLike(postId);

    if (response.success) {
      // Update UI
      const postElement = document.querySelector(`[data-post-id="${postId}"]`);
      const likeBtn = postElement.querySelector(".action-btn");
      const likesCount = postElement.querySelector(".likes-count");

      const isLiked = likeBtn.classList.toggle("liked");
      const currentCount = parseInt(likesCount.textContent);
      likesCount.textContent = isLiked ? currentCount + 1 : currentCount - 1;

      const svg = likeBtn.querySelector("svg");
      svg.setAttribute("fill", isLiked ? "currentColor" : "none");
    }
  } catch (error) {
    Utils.showToast("Failed to update like", "error");
  }
}

// Focus comment input
function focusComment(postId) {
  const commentsSection = document.getElementById(`comments-${postId}`);
  const input = document.getElementById(`comment-input-${postId}`);

  if (commentsSection.style.display === "none") {
    loadComments(postId);
  }

  input.focus();
}

// Load comments
async function loadComments(postId) {
  const commentsSection = document.getElementById(`comments-${postId}`);

  try {
    const response = await API.getComments(postId);

    if (response.success) {
      commentsSection.style.display = "block";
      commentsSection.innerHTML = "";

      if (response.data.length > 0) {
        response.data.forEach((comment) => {
          const commentElement = createCommentElement(comment);
          commentsSection.appendChild(commentElement);
        });
      } else {
        commentsSection.innerHTML =
          '<div style="padding: 16px; text-align: center; color: var(--text-secondary);">No comments yet</div>';
      }
    }
  } catch (error) {
    Utils.showToast("Failed to load comments", "error");
  }
}

// Create comment element
function createCommentElement(comment) {
  const div = document.createElement("div");
  div.className = "comment-item";
  div.dataset.commentId = comment.id;

  const isOwner = comment.user_id === currentUser.id;

  // Fix avatar path - replace .jpg with .svg for default avatar
  const commentAvatarPath = comment.profile_picture
    ? "uploads/" +
      comment.profile_picture.replace(
        "default-avatar.jpg",
        "default-avatar.svg",
      )
    : "uploads/default-avatar.svg";

  div.innerHTML = `
        <img src="http://localhost:8000/${commentAvatarPath}" 
             alt="${comment.username}" 
             class="avatar avatar-sm">
        <div class="comment-content">
            <div class="comment-text">
                <span class="comment-username">${comment.username}</span>
                ${comment.comment_text}
            </div>
            <div class="comment-time">${Utils.timeAgo(comment.created_at)}</div>
        </div>
        ${
          isOwner
            ? `
            <button class="comment-delete" onclick="deleteComment(${comment.id}, ${comment.post_id})">Delete</button>
        `
            : ""
        }
    `;

  return div;
}

// Handle comment key press
function handleCommentKeyPress(event, postId) {
  if (event.key === "Enter") {
    addComment(postId);
  }
}

// Add comment
async function addComment(postId) {
  const input = document.getElementById(`comment-input-${postId}`);
  const commentText = input.value.trim();

  if (!commentText) return;

  try {
    const response = await API.createComment(postId, commentText);

    if (response.success) {
      input.value = "";
      loadComments(postId);

      // Update comment count
      const postElement = document.querySelector(`[data-post-id="${postId}"]`);
      const commentCountElement = postElement.querySelector(
        ".action-btn:nth-child(2) span",
      );
      const currentCount = parseInt(commentCountElement.textContent);
      commentCountElement.textContent = currentCount + 1;
    }
  } catch (error) {
    Utils.showToast("Failed to add comment", "error");
  }
}

// Delete comment
async function deleteComment(commentId, postId) {
  if (!confirm("Are you sure you want to delete this comment?")) return;

  try {
    const response = await API.deleteComment(commentId);

    if (response.success) {
      Utils.showToast("Comment deleted", "success");
      loadComments(postId);

      // Update comment count
      const postElement = document.querySelector(`[data-post-id="${postId}"]`);
      const commentCountElement = postElement.querySelector(
        ".action-btn:nth-child(2) span",
      );
      const currentCount = parseInt(commentCountElement.textContent);
      commentCountElement.textContent = Math.max(0, currentCount - 1);
    }
  } catch (error) {
    Utils.showToast("Failed to delete comment", "error");
  }
}

// Delete post
async function deletePost(postId) {
  if (!confirm("Are you sure you want to delete this post?")) return;

  try {
    const response = await API.deletePost(postId);

    if (response.success) {
      Utils.showToast("Post deleted", "success");
      const postElement = document.querySelector(`[data-post-id="${postId}"]`);
      postElement.remove();
    }
  } catch (error) {
    Utils.showToast("Failed to delete post", "error");
  }
}

// Setup event listeners
function setupEventListeners() {
  // Create post button
  const createPostBtn = document.getElementById("createPostBtn");
  if (createPostBtn) {
    createPostBtn.addEventListener("click", openCreatePostModal);
  }

  // Logout button
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      API.clearAuth();
      window.location.href = "login.html";
    });
  }
}

// Create post modal
function openCreatePostModal() {
  const modal = document.getElementById("createPostModal");
  modal.classList.remove("hidden");
}

function closeCreatePostModal() {
  const modal = document.getElementById("createPostModal");
  modal.classList.add("hidden");
  document.getElementById("createPostForm").reset();
  document.getElementById("imagePreviewContainer").classList.add("hidden");
}

// Handle image selection
const imageInput = document.getElementById("imageInput");
if (imageInput) {
  imageInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        document.getElementById("imagePreview").src = e.target.result;
        document
          .getElementById("imagePreviewContainer")
          .classList.remove("hidden");
      };
      reader.readAsDataURL(file);
    }
  });
}

// Remove image
function removeImage() {
  document.getElementById("imageInput").value = "";
  document.getElementById("imagePreviewContainer").classList.add("hidden");
}

// Create post form submission
const createPostForm = document.getElementById("createPostForm");
if (createPostForm) {
  createPostForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData();
    const caption = document.getElementById("caption").value.trim();
    const imageFile = document.getElementById("imageInput").files[0];

    formData.append("caption", caption);
    if (imageFile) {
      formData.append("image", imageFile);
    }

    const submitBtn = createPostForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = "Posting...";

    try {
      const response = await API.createPost(formData);

      if (response.success) {
        Utils.showToast("Post created successfully!", "success");
        closeCreatePostModal();
        loadFeed();
      }
    } catch (error) {
      Utils.showToast(error.message || "Failed to create post", "error");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
}

// Make functions global
window.toggleLike = toggleLike;
window.focusComment = focusComment;
window.addComment = addComment;
window.deleteComment = deleteComment;
window.deletePost = deletePost;
window.handleCommentKeyPress = handleCommentKeyPress;
window.closeCreatePostModal = closeCreatePostModal;
window.removeImage = removeImage;
