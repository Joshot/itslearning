import pandas as pd
from pgmpy.models import BayesianNetwork
from pgmpy.factors.discrete import TabularCPD
from pgmpy.inference import VariableElimination
import json
import sys

def load_input():
    try:
        return json.loads(sys.argv[1])
    except (IndexError, json.JSONDecodeError):
        return {'errors': {'easy': 0, 'medium': 0, 'hard': 0}, 'failed_tasks': [], 'scores': {}}

def calculate_difficulty_distribution(errors, failed_tasks, scores):
    questions_per_task = {'easy': 4, 'medium': 3, 'hard': 3}
    num_failed_tasks = len(failed_tasks)
    if num_failed_tasks == 0:
        return {
            'distribution': {'easy': 10, 'medium': 5, 'hard': 5},
            'task_distribution': {},
            'weights': {'easy': 5.0, 'medium': 10.0, 'hard': 15.0}
        }

    errors = {d: errors.get(d, 0) for d in ['easy', 'medium', 'hard']}
    total_questions = sum(questions_per_task.values())

    # Prior berdasarkan jumlah soal per tugas
    prior = {d: questions_per_task[d] / total_questions for d in questions_per_task}

    # Hitung error rate dan success rate
    error_rates = {}
    success_rates = {}
    for difficulty in ['easy', 'medium', 'hard']:
        total_attempts = questions_per_task[difficulty] * num_failed_tasks
        error_rates[difficulty] = min(0.99, max(0.01, errors[difficulty] / total_attempts if total_attempts else 0.01))
        success_rates[difficulty] = 1 - error_rates[difficulty]

    try:
        # Setup Bayesian Network
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
                [error_rates['easy'], error_rates['medium'], error_rates['hard']],
                [success_rates['easy'], success_rates['medium'], success_rates['hard']]
            ],
            evidence=['Difficulty'],
            evidence_card=[3],
            state_names={'Error': [True, False], 'Difficulty': ['easy', 'medium', 'hard']}
        )
        model.add_cpds(cpd_difficulty, cpd_error)
        model.check_model()

        # Inferensi untuk posterior sukses
        inference = VariableElimination(model)
        posterior = inference.query(variables=['Difficulty'], evidence={'Error': False})
        success_dist = {
            'easy': posterior.values[0],
            'medium': posterior.values[1],
            'hard': posterior.values[2]
        }
    except Exception as e:
        # Fallback pakai prior sukses
        total_success = sum(success_rates[d] * prior[d] for d in success_rates)
        success_dist = {d: (success_rates[d] * prior[d]) / total_success for d in success_rates}
        print(f"BN error: {str(e)}, using fallback distribution", file=sys.stderr)

    # Distribusi soal proporsional ke posterior sukses
    total_success = sum(success_dist.values())
    distribution = {d: success_dist[d] / total_success for d in success_dist}

    num_questions = 20
    questions_per_difficulty = {
        'easy': round(num_questions * distribution['easy']),
        'medium': round(num_questions * distribution['medium']),
        'hard': round(num_questions * distribution['hard'])
    }

    # Sesuaikan total soal
    total = sum(questions_per_difficulty.values())
    if total > num_questions:
        excess = total - num_questions
        for diff in ['hard', 'medium']:
            if excess <= 0:
                break
            reduce = min(excess, questions_per_difficulty[diff] - 1)
            questions_per_difficulty[diff] -= reduce
            excess -= reduce
    elif total < num_questions:
        deficit = num_questions - total
        questions_per_difficulty['easy'] += deficit

    # Pastikan minimal 1 soal dan easy > medium >= hard
    for difficulty in questions_per_difficulty:
        questions_per_difficulty[difficulty] = max(1, questions_per_difficulty[difficulty])
    if questions_per_difficulty['medium'] < questions_per_difficulty['hard']:
        questions_per_difficulty['medium'], questions_per_difficulty['hard'] = (
            questions_per_difficulty['hard'], questions_per_difficulty['medium']
        )
    if questions_per_difficulty['easy'] <= questions_per_difficulty['medium']:
        questions_per_difficulty['easy'] = questions_per_difficulty['medium'] + 1
        questions_per_difficulty['hard'] -= 1
        if questions_per_difficulty['hard'] < 1:
            questions_per_difficulty['hard'] = 1
            questions_per_difficulty['medium'] -= 1

    # Distribusi ke tugas gagal
    task_distribution = {task: {'easy': 0, 'medium': 0, 'hard': 0} for task in failed_tasks}
    for difficulty, count in questions_per_difficulty.items():
        base_count = count // num_failed_tasks
        remainder = count % num_failed_tasks
        sorted_tasks = sorted(scores.items(), key=lambda x: x[1]) if scores else [(task, 0) for task in failed_tasks]
        task_indices = [task for task, _ in sorted_tasks if task in failed_tasks]

        for i, task in enumerate(task_indices):
            task_distribution[task][difficulty] = base_count
            if i < remainder:
                task_distribution[task][difficulty] += 1

    # Hitung bobot
    base_weights = {'easy': 5.0, 'medium': 10.0, 'hard': 15.0}
    base_questions = {'easy': 4, 'medium': 3, 'hard': 3}
    weights = {
        d: base_weights[d] * min(2, questions_per_difficulty[d] / base_questions[d])
        for d in ['easy', 'medium', 'hard']
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
