<script>
document.addEventListener('DOMContentLoaded', function() {
    const starInputs = document.querySelectorAll('input[name="rating"]');
    const stars = document.querySelectorAll('label svg');
    
    starInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            updateStars(parseInt(this.value));
        });
    });
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = index + 1;
            document.querySelector(`input[name="rating"][value="${rating}"]`).checked = true;
            updateStars(rating);
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = index + 1;
            highlightStars(rating);
        });
    });
    
    const starContainer = document.querySelector('.flex.space-x-2');
    if (starContainer) {
        starContainer.addEventListener('mouseleave', function() {
            const checkedInput = document.querySelector('input[name="rating"]:checked');
            if (checkedInput) {
                updateStars(parseInt(checkedInput.value));
            } else {
                updateStars(0);
            }
        });
    }
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
    
    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
});
</script>
