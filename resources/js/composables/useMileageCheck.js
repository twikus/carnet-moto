import { ref, watch } from 'vue'

export function useMileageCheck(form, currentMileage) {
    const showMileageProposal = ref(false)

    watch(() => Number(form.mileage), (newVal) => {
        showMileageProposal.value = newVal > currentMileage
        if (!showMileageProposal.value) {
            form.update_mileage_log = false
        }
    })

    return { showMileageProposal }
}
