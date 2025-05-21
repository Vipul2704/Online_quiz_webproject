document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent form from submitting

    // Fetch form values
    const fullName = document.getElementById('fullName').value.trim();
    const enrollmentId = document.getElementById('enrollmentId').value.trim();
    const dob = document.getElementById('dob').value;
    const gender = document.querySelector('input[name="gender"]:checked');
    const mobile = document.getElementById('mobile').value.trim();
    const course = document.getElementById('course').value;
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const profilePhoto = document.getElementById('profilePhoto').files[0];

    // Basic validation (same as before)
    if (!gender) {
        alert('Please select your gender.');
        return;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match.');
        return;
    }

    if (profilePhoto) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(profilePhoto.type)) {
            alert('Only JPEG, PNG, and GIF formats are allowed for profile photo.');
            return;
        }
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (profilePhoto.size > maxSize) {
            alert('Profile photo size should not exceed 2MB.');
            return;
        }
    } else {
        alert('Please upload your profile photo.');
        return;
    }

    // Generate username (first name + last three digits of enrollment ID)
    const firstName = fullName.split(' ')[0];
    const lastThreeDigits = enrollmentId.slice(-3);
    const generatedUsername = firstName + lastThreeDigits;

    // Add the generated username to the form
    const usernameInput = document.createElement('input');
    usernameInput.type = 'hidden';
    usernameInput.name = 'generatedUsername';
    usernameInput.value = generatedUsername;
    this.appendChild(usernameInput);

    // Show popup with generated username
    const modal = document.getElementById('usernameModal');
    const usernameMessage = document.getElementById('usernameMessage');
    usernameMessage.textContent = `Your Username: ${generatedUsername}`;

    modal.style.display = 'flex';

    // Submit the form after a short delay
    setTimeout(() => {
        this.submit();
    }, 8000); // 2 seconds delay

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
});
