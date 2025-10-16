document.querySelectorAll(".status").forEach(select => {
  select.addEventListener("change", () => {
    updateTicket(select.dataset.id, "status", select.value);
  });
});

document.querySelectorAll(".priority").forEach(select => {
  select.addEventListener("change", () => {
    updateTicket(select.dataset.id, "priority", select.value);
  });
});

document.querySelectorAll(".reassign-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const email = prompt("Enter email of user to assign this ticket to:");
    if (email) updateTicket(btn.dataset.id, "reassign", email);
  });
});

document.querySelectorAll(".comment-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const comment = prompt("Enter your comment:");
    if (comment) updateTicket(btn.dataset.id, "comment", comment);
  });
});

function updateTicket(id, type, value) {
  fetch("update_ticket.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&type=${type}&value=${encodeURIComponent(value)}`
  })
  .then(res => res.text())
  .then(msg => alert(msg))
  .catch(err => alert("Error updating ticket"));
}
