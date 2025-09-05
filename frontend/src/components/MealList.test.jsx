import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import MealList from './MealList';

describe('MealList', () => {
  const mockMeals = [
    { id: 1, name: '朝食', calories: 400, date: '2024-01-01' },
    { id: 2, name: '昼食', calories: 600, date: '2024-01-01' },
    { id: 3, name: '夕食', calories: 800, date: '2024-01-01' },
  ];

  it('renders all meals', () => {
    render(<MealList meals={mockMeals} onDelete={() => {}} />);
    
    expect(screen.getByText('朝食')).toBeInTheDocument();
    expect(screen.getByText('昼食')).toBeInTheDocument();
    expect(screen.getByText('夕食')).toBeInTheDocument();
  });

  it('displays calories for each meal', () => {
    render(<MealList meals={mockMeals} onDelete={() => {}} />);
    
    expect(screen.getByText('400 kcal')).toBeInTheDocument();
    expect(screen.getByText('600 kcal')).toBeInTheDocument();
    expect(screen.getByText('800 kcal')).toBeInTheDocument();
  });

  it('shows empty message when no meals', () => {
    render(<MealList meals={[]} onDelete={() => {}} />);
    
    expect(screen.getByText(/記録された食事がありません/i)).toBeInTheDocument();
  });

  it('calls onDelete when delete button is clicked', async () => {
    const user = userEvent.setup();
    const mockOnDelete = vi.fn();
    render(<MealList meals={mockMeals} onDelete={mockOnDelete} />);

    const deleteButtons = screen.getAllByRole('button', { name: /削除/i });
    await user.click(deleteButtons[0]);

    expect(mockOnDelete).toHaveBeenCalledWith(1);
  });

  it('groups meals by date', () => {
    const mealsWithDifferentDates = [
      { id: 1, name: '朝食', calories: 400, date: '2024-01-01' },
      { id: 2, name: '昼食', calories: 600, date: '2024-01-02' },
    ];
    
    render(<MealList meals={mealsWithDifferentDates} onDelete={() => {}} />);
    
    expect(screen.getByText('2024-01-01')).toBeInTheDocument();
    expect(screen.getByText('2024-01-02')).toBeInTheDocument();
  });
});