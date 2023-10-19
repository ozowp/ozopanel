import { useState } from 'react';
import "./style.css";

function App() {
  const [activeMenu, setActiveMenu] = useState("general");
  const [menus, setMenus] = useState([
    {
      id: "general",
      title: "General",
      subMenus: [
        {
          id: "profile",
          title: "Profile",
          formFields: [
            {
              id: "firstName",
              label: "First Name",
              value: "",
              type: "text",
              required: true,
            },
            {
              id: "lastName",
              label: "Last Name",
              value: "",
              type: "text",
              required: true,
            },
            {
              id: "email",
              label: "Email",
              value: "",
              type: "email",
              required: true,
            },
          ],
        },
        {
          id: "preferences",
          title: "Preferences",
          formFields: [
            {
              id: "notifications",
              label: "Notifications",
              value: "",
              type: "checkbox",
              required: false,
            },
            {
              id: "language",
              label: "Language",
              value: "",
              type: "text",
              required: false,
            },
          ],
        },
      ],
    },
    {
      id: "test",
      title: "Test",
      subMenus: [
        {
          id: "profile",
          title: "Profile",
          formFields: [
            {
              id: "firstName",
              label: "First Name",
              value: "",
              type: "text",
              required: true,
            },
            {
              id: "lastName",
              label: "Last Name",
              value: "",
              type: "text",
              required: true,
            },
            {
              id: "email",
              label: "Email",
              value: "",
              type: "email",
              required: true,
            },
          ],
        },
        {
          id: "preferences",
          title: "Preferences",
          formFields: [
            {
              id: "notifications",
              label: "Notifications",
              value: "",
              type: "checkbox",
              required: false,
            },
            {
              id: "language",
              label: "Language",
              value: "",
              type: "text",
              required: false,
            },
          ],
        },
      ],
    },
  ]);

  const handleMenuClick = (menu) => {
    setActiveMenu(menu);
  };

  const Menu = ({ title, subMenus }) => {
    return (
      <div className="menu">
        <h3>{title}</h3>
        <ul>
          {subMenus.map((subMenu) => (
            <li key={subMenu.id} onClick={() => handleMenuClick(subMenu.id)}>
              {subMenu.title}
            </li>
          ))}
        </ul>
      </div>
    );
  };

  const FormFields = ({ formFields }) => {
    const handleFormSubmit = (event) => {
      event.preventDefault();
      // handle form submission logic here
    };

    const handleChange = (event) => {
      const { name, value } = event.target;
      setFormFields((prevFormFields) =>
        prevFormFields.map((field) =>
          field.id === name ? { ...field, value } : field
        )
      );
    };

    return (
      <form onSubmit={handleFormSubmit}>
        {formFields.map((field) => (
          <label key={field.id}>
            {field.label}:
            <input
              type={field.type}
              name={field.id}
              value={field.value}
              required={field.required}
              onChange={handleChange}
            />
          </label>
        ))}
        <button type="submit">Save</button>
      </form>
    );
  };

  const activeMenuData = menus.find((menu) => menu.id === activeMenu);

  return (
    <div className="App">
      <div className="sidebar">
        <h2>WAM Settings</h2>
        <div className="menu-container">
          <Menu title="Menus" subMenus={menus} />
        </div>
      </div>
      <div className="content">
        <h3>{activeMenuData.title}</h3>
        {activeMenuData.subMenus.map((subMenu) => (
          <div key={subMenu.id}>
            <h4>{subMenu.title}</h4>
            <FormFields formFields={subMenu.formFields} />
          </div>
        ))}
      </div>
    </div>
  );
}

export default App;
