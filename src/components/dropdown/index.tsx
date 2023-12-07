import React, { useState, useRef, useEffect } from 'react'

interface DropdownRowProps {
  style?: React.CSSProperties
  children: React.ReactNode
}

export const DropdownRow: React.FC<DropdownRowProps> = ({ style, children }) => {
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
    <div className="ozop-dropdown-row relative" ref={dropdownRef} style={style}>
      <button className="" type="button" onClick={toggleDropdown}>
        <svg
          width={24}
          height={24}
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M10 19c0 1.1.9 2 2 2s2-.9 2-2-.9-2-2-2-2 .9-2 2zM10 5c0 1.1.9 2 2 2s2-.9 2-2-.9-2-2-2-2 .9-2 2zM10 12c0 1.1.9 2 2 2s2-.9 2-2-.9-2-2-2-2 .9-2 2z"
            fill="#1A263A"
          />
        </svg>
      </button>
      <ul
        className={`absolute z-[5] float-left border m-0 ${isDropdownOpen ? '' : 'hidden'
          } min-w-max list-none overflow-hidden rounded-lg bg-white bg-clip-padding text-left text-base shadow-lg dark:bg-neutral-700 [&[data-te-dropdown-show]]:block`}
      >
        {children}
      </ul>
    </div>
  )
}
