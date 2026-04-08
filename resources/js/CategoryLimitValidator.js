/**
 * CategoryLimitValidator.js
 * 
 * Frontend utility to check event category registration limits
 * Prevents form submission if category limit is exceeded
 */

export class CategoryLimitValidator {
    /**
     * Check if a category has available slots
     * @param {number} categoryId - The event category ID
     * @param {Object} category - The category object with limit and current count
     * @returns {Object} - { available: boolean, slotsRemaining: number, message: string }
     */
    static checkAvailability(category) {
        if (!category) {
            return {
                available: false,
                slotsRemaining: 0,
                message: 'Kategori tidak ditemukan'
            };
        }

        const registrationCount = category.registration_count || 0;
        const limit = category.limit || 200;
        const slotsRemaining = Math.max(0, limit - registrationCount);
        const available = slotsRemaining > 0;

        return {
            available,
            slotsRemaining,
            registrationCount,
            limit,
            message: available 
                ? `Sisa kuota: ${slotsRemaining} dari ${limit}`
                : `Maaf, kuota untuk kategori '${category.name}' telah penuh`
        };
    }

    /**
     * Get multiple categories' availability status
     * @param {Array} categories - Array of category objects
     * @returns {Array} - Array of availability status for each category
     */
    static checkMultipleAvailability(categories) {
        return categories.map(category => ({
            id: category.id,
            name: category.name,
            ...this.checkAvailability(category)
        }));
    }

    /**
     * Validate form before submission
     * @param {HTMLElement} form - The form element
     * @param {Object} selectedCategory - The selected category object
     * @returns {boolean} - True if valid, false otherwise
     */
    static validateForm(form, selectedCategory) {
        const availability = this.checkAvailability(selectedCategory);
        
        if (!availability.available) {
            // Prevent form submission
            alert(availability.message);
            return false;
        }

        return true;
    }

    /**
     * Display availability status in the UI
     * @param {HTMLElement} element - The element to display status in
     * @param {Object} category - The category object
     * @param {Object} options - Display options
     */
    static displayStatus(element, category, options = {}) {
        const availability = this.checkAvailability(category);
        const className = availability.available ? 'text-success' : 'text-danger';
        
        let html = `<small class="${className}">`;
        html += availability.message;
        
        if (availability.available && options.showPercentage) {
            const percentage = Math.round((availability.registrationCount / availability.limit) * 100);
            html += ` (${percentage}% terisi)`;
        }
        
        html += '</small>';
        
        element.innerHTML = html;
    }

    /**
     * Fetch category availability from server
     * @param {number} categoryId - The event category ID
     * @returns {Promise<Object>} - Category availability data
     */
    static async fetchCategoryAvailability(categoryId) {
        try {
            const response = await fetch(`/api/categories/${categoryId}/availability`);
            if (!response.ok) {
                throw new Error('Failed to fetch category availability');
            }
            return await response.json();
        } catch (error) {
            console.error('Error fetching category availability:', error);
            return null;
        }
    }

    /**
     * Real-time validation on category select change
     * @param {HTMLSelectElement} selectElement - The category select element
     * @param {HTMLElement} statusElement - The element to display status in
     * @param {Array} categories - Available categories
     */
    static setupRealTimeValidation(selectElement, statusElement, categories) {
        selectElement.addEventListener('change', (e) => {
            const categoryId = e.target.value;
            const selectedCategory = categories.find(c => c.id == categoryId);
            
            if (selectedCategory) {
                this.displayStatus(statusElement, selectedCategory, { showPercentage: true });
            }
        });
    }
}

export default CategoryLimitValidator;
