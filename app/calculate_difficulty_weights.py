import pandas as pd
from pgmpy.models import BayesianNetwork
from pgmpy.factors.discrete import TabularCPD
from pgmpy.inference import VariableElimination
import json
import sys

def load_input():
    try:
        return json.loads(sys.argv[1])
    except IndexError:
        raise ValueError("No input provided")
    except json.JSONDecodeError:
        raise ValueError("Invalid JSON input")

def calculate_difficulty_distribution(errors, failed_tasks, scores):
    # Asumsi jumlah soal per tugas: 4 easy, 3 medium, 3 hard (total 10 soal/tugas)
    questions_per_task = {'easy': 4, 'medium': 3, 'hard': 3}
    total_questions = sum(questions_per_task.values()) * max(1, len(failed_tasks))

    # Hitung prior P(Difficulty) dari errors
    total_errors = sum(errors.get(d, 0) for d in ['easy', 'medium', 'hard'])
    if total_errors == 0:
        prior = {'easy': 1/3, 'medium': 1/3, 'hard': 1/3}  # Uniform kalau nggak ada error
    else:
        prior = {
            'easy': errors.get('easy', 0) / total_errors,
            'medium': errors.get('medium', 0) / total_errors,
            'hard': errors.get('hard', 0) / total_errors
        }

    # Hitung P(Error=True|Difficulty) dari errors
    error_rates = {}
    for difficulty in ['easy', 'medium', 'hard']:
        if total_questions == 0 or questions_per_task[difficulty] == 0:
            error_rates[difficulty] = 0.5  # Default kalau nggak ada data
        else:
            error_count = errors.get(difficulty, 0)
            total_attempts = questions_per_task[difficulty] * max(1, len(failed_tasks))
            error_rates[difficulty] = min(0.99, max(0.01, error_count / total_attempts))

    # Definisikan Bayesian Network
    model = BayesianNetwork([('Difficulty', 'Error')])
    cpd_difficulty = TabularCPD(
        variable='Difficulty',
        variable_card=3,
        values=[[prior['easy']], [prior['medium']], [prior['hard']]],
        state_names={'Difficulty': ['easy', 'medium', 'hard']}
    )
    cpd_error = TabularCPD(
        variable='Error',
        variable_card=2,
        values=[
            [error_rates['easy'], error_rates['medium'], error_rates['hard']],  # P(Error=True|Difficulty)
            [1 - error_rates['easy'], 1 - error_rates['medium'], 1 - error_rates['hard']]  # P(Error=False|Difficulty)
        ],
        evidence=['Difficulty'],
        evidence_card=[3],
        state_names={'Error': [True, False], 'Difficulty': ['easy', 'medium', 'hard']}
    )
    model.add_cpds(cpd_difficulty, cpd_error)
    model.check_model()

    # Hitung posterior P(Difficulty|Error=True)
    inference = VariableElimination(model)
    posterior = inference.query(variables=['Difficulty'], evidence={'Error': True})
    error_dist = {
        'easy': posterior.values[0],
        'medium': posterior.values[1],
        'hard': posterior.values[2]
    }

    # Distribusi soal: Invers proporsional ke P(Difficulty|Error=True)
    num_questions = 20
    inverse_dist = {d: 1 / max(0.01, error_dist[d]) for d in error_dist}
    total_inverse = sum(inverse_dist.values())
    distribution = {d: inverse_dist[d] / total_inverse for d in inverse_dist}

    # Hitung jumlah soal per kesulitan
    questions_per_difficulty = {
        'easy': round(num_questions * distribution['easy']),
        'medium': round(num_questions * distribution['medium']),
        'hard': round(num_questions * distribution['hard'])
    }

    # Sesuaikan total agar tepat 20 soal
    total = sum(questions_per_difficulty.values())
    if total > num_questions:
        excess = total - num_questions
        max_key = max(questions_per_difficulty, key=questions_per_difficulty.get)
        questions_per_difficulty[max_key] -= excess
    elif total < num_questions:
        deficit = num_questions - total
        min_key = min(questions_per_difficulty, key=questions_per_difficulty.get)
        questions_per_difficulty[min_key] += deficit

    # Pastikan minimal 1 soal per kesulitan
    for difficulty in questions_per_difficulty:
        questions_per_difficulty[difficulty] = max(1, questions_per_difficulty[difficulty])

    # Bagi soal per tugas yang tidak lulus
    num_failed_tasks = max(1, len(failed_tasks))
    task_distribution = {task: {'easy': 0, 'medium': 0, 'hard': 0} for task in failed_tasks}
    for difficulty, count in questions_per_difficulty.items():
        if not failed_tasks:
            task_distribution[0] = {difficulty: count}
            continue
        # Bagi rata
        base_count = count // num_failed_tasks
        remainder = count % num_failed_tasks
        # Urutkan tugas berdasarkan nilai (ascending)
        sorted_tasks = sorted(scores.items(), key=lambda x: x[1]) if scores else [(task, 0) for task in failed_tasks]
        task_indices = [task for task, _ in sorted_tasks if task in failed_tasks]

        for i, task in enumerate(task_indices):
            task_distribution[task][difficulty] = base_count
            if i < remainder:
                task_distribution[task][difficulty] += 1

    # Hitung bobot berdasarkan distribusi
    weights = {
        'easy': 5.0 * (1 + distribution['easy']),
        'medium': 9.0 * (1 + distribution['medium']),
        'hard': 15.0 * (1 + distribution['hard'])
    }

    total_distribution = {
        'easy': sum(d['easy'] for d in task_distribution.values()),
        'medium': sum(d['medium'] for d in task_distribution.values()),
        'hard': sum(d['hard'] for d in task_distribution.values())
    }

    return {
        'distribution': total_distribution,
        'task_distribution': task_distribution,
        'weights': weights
    }

def main():
    input_data = load_input()
    errors = input_data.get('errors', {})
    failed_tasks = input_data.get('failed_tasks', [])
    scores = input_data.get('scores', {})
    result = calculate_difficulty_distribution(errors, failed_tasks, scores)
    print(json.dumps(result))

if __name__ == "__main__":
    main()
