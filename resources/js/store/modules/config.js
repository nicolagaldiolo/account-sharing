const { sharingsVisibility, renewalFrequency } = window.config

// state
export const state = {
  sharingsVisibility,
  renewalFrequency
}

// getters
export const getters = {
  sharingsVisibility: state => state.sharingsVisibility,
  renewalFrequency: state => state.renewalFrequency
}
