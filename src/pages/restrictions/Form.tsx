import React, { FC, useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@blocks/preloader/spinner';
import api from '@utils/api';

interface FormData {
    name: string;
    email: string;
    // Add other form fields as needed
}

const Form: FC = () => {
    const { type, id } = useParams();
    const navigate = useNavigate();

    const [formData, setFormData] = useState<FormData>({
        name: '',
        email: '',
        // Initialize other form fields here
    });

    const [loadingFetch, setLoadingFetch] = useState(true);
    const [loadingSubmit, setLoadingSubmit] = useState(false);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const idParam = id ? `/${id}` : `/0`;
                const res = await api.get(`restrictions/${type}${idParam}`);
                if (res.success) {
                    // Populate the form with fetched data
                    // setFormData(res.data);
                } else {
                    res.data.forEach((value: string) => {
                        toast.error(value);
                    });
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                setLoadingFetch(false);
            }
        };

        fetchData();
    }, [type, id]);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        try {
            setLoadingSubmit(true);
            const idParam = id ? `/${id}` : '';
            const res = await api.add(`restrictions/${type}${idParam}`, formData);
            if (res.success) {
                toast.success('Data submitted successfully');
                navigate(`/restrictions/${type}`);
            } else {
                res.data.forEach((value: string) => {
                    toast.error(value);
                });
            }
        } catch (error) {
            console.error('Error submitting data:', error);
        } finally {
            setLoadingSubmit(false);
        }
    };

    const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = event.target;
        setFormData({ ...formData, [name]: value });
    };

    const i18n = wam.i18n;

    return (
        <div className='wam-users-form'>
            <h3>{`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}</h3>
            <button className='' onClick={() => navigate(`/restrictions/${type}`)}>
                {`${i18n.backTo} ${type === 'users' ? i18n.users : i18n.roles}`}
            </button>

            {loadingFetch ? <Spinner /> : (
                <form onSubmit={handleSubmit}>
                    {/* Form fields */}
                    <label>
                        Name:
                        <input
                            type='text'
                            name='name'
                            value={formData.name}
                            onChange={handleInputChange}
                            required
                        />
                    </label>
                    <label>
                        Email:
                        <input
                            type='email'
                            name='email'
                            value={formData.email}
                            onChange={handleInputChange}
                            required
                        />
                    </label>
                    {/* Add other form fields here */}
                    <button type='submit' disabled={loadingSubmit}>
                        {loadingSubmit ? 'Submitting...' : 'Submit'}
                    </button>
                </form>
            )}
        </div>
    );
};

export default Form;
