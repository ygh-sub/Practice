import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import MealForm from './MealForm';

describe('MealForm', () => {
  it('renders all input fields', () => {
    render(<MealForm onSubmit={() => {}} />);
    
    expect(screen.getByLabelText(/食事名/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/カロリー/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/日付/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /追加/i })).toBeInTheDocument();
  });

  it('calls onSubmit with form data when submitted', async () => {
    const user = userEvent.setup();
    const mockOnSubmit = vi.fn();
    render(<MealForm onSubmit={mockOnSubmit} />);

    const nameInput = screen.getByLabelText(/食事名/i);
    const caloriesInput = screen.getByLabelText(/カロリー/i);
    const dateInput = screen.getByLabelText(/日付/i);
    const submitButton = screen.getByRole('button', { name: /追加/i });

    await user.type(nameInput, '朝食');
    await user.type(caloriesInput, '500');
    await user.clear(dateInput);
    await user.type(dateInput, '2024-01-01');
    await user.click(submitButton);

    await waitFor(() => {
      expect(mockOnSubmit).toHaveBeenCalledWith({
        name: '朝食',
        calories: 500,
        date: '2024-01-01',
      });
    });
  });

  it('resets form after successful submission', async () => {
    const user = userEvent.setup();
    const mockOnSubmit = vi.fn();
    render(<MealForm onSubmit={mockOnSubmit} />);

    const nameInput = screen.getByLabelText(/食事名/i);
    const caloriesInput = screen.getByLabelText(/カロリー/i);

    await user.type(nameInput, '昼食');
    await user.type(caloriesInput, '600');
    await user.click(screen.getByRole('button', { name: /追加/i }));

    await waitFor(() => {
      expect(nameInput.value).toBe('');
      expect(caloriesInput.value).toBe('');
    });
  });

  it('validates required fields', async () => {
    const user = userEvent.setup();
    const mockOnSubmit = vi.fn();
    render(<MealForm onSubmit={mockOnSubmit} />);

    const submitButton = screen.getByRole('button', { name: /追加/i });
    await user.click(submitButton);

    expect(mockOnSubmit).not.toHaveBeenCalled();
  });
});