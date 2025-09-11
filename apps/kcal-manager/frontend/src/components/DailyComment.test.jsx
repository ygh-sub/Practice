import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import DailyComment from './DailyComment';
import mealApi from '../api/mealApi';

vi.mock('../api/mealApi');

describe('DailyComment', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders component with date', () => {
    render(<DailyComment date="2024-01-01" hasMeals={true} />);
    
    expect(screen.getByText('AIアドバイス')).toBeInTheDocument();
    expect(screen.getByText('2024-01-01')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /AIアドバイスを生成/i })).toBeInTheDocument();
  });

  it('disables button when no meals', () => {
    render(<DailyComment date="2024-01-01" hasMeals={false} />);
    
    const button = screen.getByRole('button', { name: /AIアドバイスを生成/i });
    expect(button).toBeDisabled();
    expect(screen.getByText(/食事を記録してからアドバイスを生成してください/i)).toBeInTheDocument();
  });

  it('generates and displays comment', async () => {
    const user = userEvent.setup();
    const mockComment = 'バランスの良い食事です。';
    
    mealApi.generateComment.mockResolvedValue({
      comment: mockComment,
      date: '2024-01-01',
      meal_count: 3,
      total_calories: 1800
    });

    render(<DailyComment date="2024-01-01" hasMeals={true} />);
    
    const button = screen.getByRole('button', { name: /AIアドバイスを生成/i });
    await user.click(button);

    await waitFor(() => {
      expect(screen.getByText(mockComment)).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /クリア/i })).toBeInTheDocument();
    });
  });

  it('shows error when comment generation fails', async () => {
    const user = userEvent.setup();
    mealApi.generateComment.mockRejectedValue(new Error('API Error'));

    render(<DailyComment date="2024-01-01" hasMeals={true} />);
    
    const button = screen.getByRole('button', { name: /AIアドバイスを生成/i });
    await user.click(button);

    await waitFor(() => {
      expect(screen.getByText(/コメントの生成に失敗しました/i)).toBeInTheDocument();
    });
  });

  it('clears comment when clear button is clicked', async () => {
    const user = userEvent.setup();
    const mockComment = 'テストコメント';
    
    mealApi.generateComment.mockResolvedValue({
      comment: mockComment
    });

    render(<DailyComment date="2024-01-01" hasMeals={true} />);
    
    const generateButton = screen.getByRole('button', { name: /AIアドバイスを生成/i });
    await user.click(generateButton);

    await waitFor(() => {
      expect(screen.getByText(mockComment)).toBeInTheDocument();
    });

    const clearButton = screen.getByRole('button', { name: /クリア/i });
    await user.click(clearButton);

    expect(screen.queryByText(mockComment)).not.toBeInTheDocument();
    expect(screen.getByRole('button', { name: /AIアドバイスを生成/i })).toBeInTheDocument();
  });
});