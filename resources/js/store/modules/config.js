const { sharingsVisibility, renewalFrequency, dayRefundLimit } = window.config

// state
export const state = {
  sharingsVisibility,
  renewalFrequency,
  dayRefundLimit
}

// getters
export const getters = {
  sharingsVisibility: state => state.sharingsVisibility,
  renewalFrequency: state => state.renewalFrequency,
  dayRefundLimit: state => state.dayRefundLimit
}
