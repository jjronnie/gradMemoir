<h1>New Course / University Application</h1>

<p><strong>Name:</strong> {{ $application->applicant_name }}</p>
<p><strong>Email:</strong> {{ $application->email }}</p>
<p><strong>Phone:</strong> {{ $application->phone }}</p>
<p><strong>University:</strong> {{ $application->university_name }}</p>
<p><strong>Course:</strong> {{ $application->course_name }}</p>
<p><strong>Year:</strong> {{ $application->year }}</p>

@if ($application->notes)
    <p><strong>Notes:</strong></p>
    <p>{{ $application->notes }}</p>
@endif
