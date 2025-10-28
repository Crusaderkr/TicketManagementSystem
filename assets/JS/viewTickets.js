function openTicket(ticketId) {
  // Redirect to ticket_details.php with the selected ticket ID
  window.location.href = `ticket_details.php?id=${ticketId}`;
}