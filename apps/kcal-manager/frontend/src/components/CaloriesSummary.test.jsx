import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import CaloriesSummary from './CaloriesSummary';

describe('CaloriesSummary', () => {
  it('displays correct total calories', () => {
    const meals = [
      { id: 1, name: '朝食', calories: 400, date: '2024-01-01' },
      { id: 2, name: '昼食', calories: 600, date: '2024-01-01' },
      { id: 3, name: '夕食', calories: 800, date: '2024-01-01' },
    ];

    render(<CaloriesSummary meals={meals} selectedDate="2024-01-01" />);
    
    expect(screen.getByText('1800')).toBeInTheDocument();
    expect(screen.getByText('kcal')).toBeInTheDocument();
  });

  it('displays zero when no meals', () => {
    render(<CaloriesSummary meals={[]} selectedDate="2024-01-01" />);
    
    expect(screen.getByText('0')).toBeInTheDocument();
  });

  it('calculates only meals for selected date', () => {
    const meals = [
      { id: 1, name: '朝食', calories: 400, date: '2024-01-01' },
      { id: 2, name: '昼食', calories: 600, date: '2024-01-01' },
      { id: 3, name: '夕食', calories: 800, date: '2024-01-02' },
    ];

    render(<CaloriesSummary meals={meals} selectedDate="2024-01-01" />);
    
    expect(screen.getByText('1000')).toBeInTheDocument();
  });

  it('displays date in summary', () => {
    render(<CaloriesSummary meals={[]} selectedDate="2024-01-01" />);
    
    expect(screen.getByText(/2024-01-01/)).toBeInTheDocument();
  });
});