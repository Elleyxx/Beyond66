import { loadPlannerDrafts, savePlannerDraft } from '@/services/plannerService'

export function getMyTrips() {
  return loadPlannerDrafts()
}

export function saveTrip(payload) {
  return savePlannerDraft(payload)
}
