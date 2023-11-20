import React, { useState, useRef, useEffect } from 'react'

interface DropdownRowProps {
  children: React.ReactNode
}

export const DropdownRow: React.FC<DropdownRowProps> = ({ children }) => {
  const [isDropdownOpen, setDropdownOpen] = useState(false)
  const dropdownRef = useRef<HTMLDivElement>(null)

  const toggleDropdown = () => {
    setDropdownOpen((prev) => !prev)
  }

  const closeDropdown = () => {
    setDropdownOpen(false)
  }

  const handleClickOutside = (event: MouseEvent) => {
    if (
      dropdownRef.current &&
      !dropdownRef.current.contains(event.target as Node)
    ) {
      closeDropdown()
    }
  }

  useEffect(() => {
    document.addEventListener('mousedown', handleClickOutside)

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  })

  return (
    <div className="relative" ref={dropdownRef}>
      <button className="" type="button" onClick={toggleDropdown}>
        Action
      </button>
      <ul
        className={`absolute z-[5] float-left m-0 ${
          isDropdownOpen ? '' : 'hidden'
        } min-w-max list-none overflow-hidden rounded-lg border-none bg-white bg-clip-padding text-left text-base shadow-lg dark:bg-neutral-700 [&[data-te-dropdown-show]]:block`}
      >
        {children}
      </ul>
    </div>
  )
}
