// Provider.tsx
import React, { createContext, useContext, useState } from 'react'
import DeleteModal from './DeleteModal'
import ProModal from './ProModal'

interface ProviderProps {
  children: React.ReactNode
}

interface ConfirmContextProps {
  delConfirm: (onConfirm: () => void, onCancel?: () => void) => void
  proAlert: (onCancel?: () => void) => void
}

const ConfirmContext = createContext<ConfirmContextProps | undefined>(undefined)

export const AlertProvider: React.FC<ProviderProps> = ({ children }) => {
  //delete confirm
  const [delModalStatus, setDelModalStaus] = useState(false)
  const [delConfirmHandler, setDelConfirmHandler] = useState<() => void>(() => {})

  //pro alert
  const [proModalStatus, setProModalStaus] = useState(false)

  const delConfirm = (onConfirm: () => void) => {
    setDelConfirmHandler(() => onConfirm)
    setDelModalStaus(true)
  }

  const proAlert = () => {
    setProModalStaus(true);
  }

  const closeDelConfirm = () => {
    setDelModalStaus(false)
  }

  const closeProAlert = () => {
    setProModalStaus(false)
  }

  return (
    <ConfirmContext.Provider value={{ delConfirm, proAlert }}>
      {children}
      <DeleteModal
        isOpen={delModalStatus}
        onCancel={() => {
          closeDelConfirm()
        }}
        onConfirm={() => {
          delConfirmHandler()
          closeDelConfirm()
        }}
      />
      <ProModal
        isOpen={proModalStatus}
        onCancel={closeProAlert}
      />
    </ConfirmContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components
export const useAlert = () => {
  const context = useContext(ConfirmContext)
  if (!context) {
    throw new Error('useAlert must be used within a Provider')
  }
  return context
}
