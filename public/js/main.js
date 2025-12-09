// public/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan fungsi JS di sini jika diperlukan, misalnya untuk konfirmasi delete
    window.confirmDelete = function(id, type, controllerUrl) {
        if (confirm("Are you sure you want to delete this " + type + "?")) {
            fetch(controllerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete&id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting ' + type + ': ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        }
    }
});