// DeleteModal.tsx
import { FC } from 'react'
import './style.scss'

interface DeleteModalProps {
  isOpen: boolean
  onCancel: () => void
  onConfirm: () => void
}

const DeleteModal: FC<DeleteModalProps> = ({ isOpen, onCancel, onConfirm }) => {
  if (!isOpen) {
    return null
  }

  return (
    <div className="ozop-delete-confirmation-modal">
      <div className="modal-content">
        <h2 className="modal-title">Confirm Deletion</h2>
        <p className="modal-message">
          Are you sure you want to delete this item?
        </p>
        <button className="modal-button confirm-btn" onClick={onConfirm}>
          Confirm
        </button>
        <button className="modal-button cancel-btn" onClick={onCancel}>
          Cancel
        </button>
      </div>
    </div>
  )
}

export default DeleteModal
