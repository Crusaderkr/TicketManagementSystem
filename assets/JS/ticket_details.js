
function confirmReassign() {
  const select = document.querySelector('select[name="reassign_to"]');
  const name = select.options[select.selectedIndex].text;

  if (!select.value) {
    alert("Please select a user to reassign the ticket.");
    return false;
  }

  return confirm(`Are you sure you want to reassign this ticket to ${name}?`);
}


function validateComment() {
  const textarea = document.getElementById("comment_text");
  const text = textarea.value.trim();

  if (text === "") {
    alert("Comment cannot be empty!");
    textarea.focus();
    return false;
  }

  return true;
}
document.addEventListener("DOMContentLoaded", () => {
  const feedback = document.querySelector(".feedback");
  if (feedback) {
    setTimeout(() => {
      feedback.style.opacity = "0";
      setTimeout(() => feedback.remove(), 600);
    }, 4000);
  }
});
