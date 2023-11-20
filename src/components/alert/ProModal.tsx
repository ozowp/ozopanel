// ProModal.tsx
import { FC } from 'react'
import './style.scss'

interface ProModalProps {
  isOpen: boolean
  onCancel: () => void
}

const ProModal: FC<ProModalProps> = ({ isOpen, onCancel }) => {
  if (!isOpen) {
    return null
  }

  return (
    <div className="ozop-delete-confirmation-modal">
      <div className="modal-content">
        <h2 className="modal-title">This is Pro component</h2>
        <p className="modal-message">To use thsi component buy pro</p>
        <button className="modal-button cancel-btn" onClick={onCancel}>
          Cancel
        </button>
      </div>
    </div>
  )
}

export default ProModal
