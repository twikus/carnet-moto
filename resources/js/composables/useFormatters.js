export function useFormatters() {
    function formatDate(dateStr) {
        return new Date(dateStr).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        })
    }

    function formatAmount(amount) {
        if (!amount) return null
        return Number(amount).toFixed(2)
    }

    function totalAmount(items) {
        const total = items.reduce((sum, item) => {
            return sum + (item.amount ? Number(item.amount) : 0)
        }, 0)
        return total > 0 ? total.toFixed(2) : null
    }

    return { formatDate, formatAmount, totalAmount }
}
