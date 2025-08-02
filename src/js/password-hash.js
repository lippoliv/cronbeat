function sha256(str) {
    // This is a simple polyfill for the SHA-256 algorithm
    // In a real application, you would use a more robust library
    return crypto.subtle.digest('SHA-256', new TextEncoder().encode(str))
        .then(buffer => {
            return Array.from(new Uint8Array(buffer))
                .map(b => b.toString(16).padStart(2, '0'))
                .join('');
        });
}

function initPasswordHashForms() {
    document.querySelectorAll('form.hash-password').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const passwordField = this.querySelector('input[name="password"]');
            if (passwordField && passwordField.value) {
                // Hash the password
                const hashedPassword = await sha256(passwordField.value);
                
                // Create a hidden field for the hashed password
                const hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = 'password_hash';
                hiddenField.value = hashedPassword;
                this.appendChild(hiddenField);
                
                // Clear the original password field
                passwordField.value = '';
                
                // Submit the form
                this.submit();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initPasswordHashForms);