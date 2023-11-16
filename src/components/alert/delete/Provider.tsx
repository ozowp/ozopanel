// Provider.tsx
import React, { createContext, useContext, useState } from 'react'
import DeleteConfirmationModal from './Modal'

interface ProviderProps {
  children: React.ReactNode
}

interface ConfirmContextProps {
  openDelConfirm: (onConfirm: () => void, onCancel?: () => void) => void
}

const ConfirmContext = createContext<ConfirmContextProps | undefined>(undefined)

export const Provider: React.FC<ProviderProps> = ({ children }) => {
  const [isDeleteConfirmationOpen, setDeleteConfirmationOpen] = useState(false)
  const [cancelHandler, setCancelHandler] = useState<() => void>(() => {})
  const [confirmHandler, setConfirmHandler] = useState<() => void>(() => {})

  const openDelConfirm = (onConfirm: () => void, onCancel?: () => void) => {
    setCancelHandler(() => onCancel || (() => {}))
    setConfirmHandler(() => onConfirm)
    setDeleteConfirmationOpen(true)
  }

  const closeDeleteConfirmation = () => {
    setDeleteConfirmationOpen(false)
  }

  return (
    <ConfirmContext.Provider value={{ openDelConfirm }}>
      {children}
      <DeleteConfirmationModal
        isOpen={isDeleteConfirmationOpen}
        onCancel={() => {
          cancelHandler()
          closeDeleteConfirmation()
        }}
        onConfirm={() => {
          confirmHandler()
          closeDeleteConfirmation()
        }}
      />
    </ConfirmContext.Provider>
  )
}

export const useDelConfirm = () => {
  const context = useContext(ConfirmContext)
  if (!context) {
    throw new Error('useDelConfirm must be used within a Provider')
  }
  return context
}
