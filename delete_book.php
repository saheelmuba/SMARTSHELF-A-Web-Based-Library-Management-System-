<?php
/**
 * admin/delete_book.php — Delete a book (CRUD: Delete)
 * -------------------------------------------------------------
 * Removes a book and its dependent records. Refuses to delete a
 * book that currently has active loans to protect data integrity.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();

// Destructive action: only accept a CSRF-protected POST, never a GET link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/manage_books.php');
}
verify_csrf();

$bookId = (int) ($_POST['id'] ?? 0);
$book = db_select_one("SELECT * FROM books WHERE book_id = ?", 'i', [$bookId]);

if (!$book) {
    set_flash('error', 'Book not found.');
    redirect('admin/manage_books.php');
}

// Block deletion if copies are still on loan.
$active = (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE book_id=? AND return_date IS NULL", 'i', [$bookId]);
if ($active > 0) {
    set_flash('error', 'Cannot delete "' . $book['title'] . '" — it has ' . $active . ' active loan(s).');
    redirect('admin/manage_books.php');
}

// Delete (foreign keys cascade-clean borrowings/reservations/reviews history).
db_execute("DELETE FROM books WHERE book_id = ?", 'i', [$bookId]);
log_activity('Deleted book "' . $book['title'] . '"');
set_flash('success', 'Book "' . $book['title'] . '" deleted.');
redirect('admin/manage_books.php');
