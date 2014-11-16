{{--var data = [--}}
  {{--{id: 1, name: "ManageGroupsCept.php", updated_at: "one minute ago", state: 'running'},--}}
  {{--{id: 2, name: "LostPasswordCept.php", updated_at: "5 seconds ago", state: 'ok'},--}}
  {{--{id: 3, name: "Offices/AddOfficeAddressCept.php", updated_at: "one minute ago", state: 'failed'},--}}
  {{--{id: 4, name: "ProfileVisitsCept.php", updated_at: "10 minutes ago", state: 'queued'},--}}
{{--];--}}

var TestsTable = React.createClass({
    getInitialState: function() {
        return {data: []};
    },

    loadCommentsFromServer: function() {
        $.ajax({
            url: this.props.url,
            dataType: 'json',
            success: function(data) {
                this.setState({data: data});
            }.bind(this),
            error: function(xhr, status, err) {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },

    componentDidMount: function() {
        this.loadCommentsFromServer();
        setInterval(this.loadCommentsFromServer, this.props.pollInterval);
    },

    render: function() {
        return (
            <TestList data={this.state.data} />
        );
    }
});

var TestList = React.createClass({
    render: function() {
        var testNodes = this.props.data.map(function (test)
        {
            return (
                <tr key={test.id}>
                    <td>{test.name}</td>
                    <td>{test.updated_at}</td>
                    <td><State type={test.state} /></td>
                </tr>
            );
        });

        return (
			<table className="table">
                <thead>
                    <tr>
                        <th width="70%">Test</th>
                        <th>Last Run</th>
                        <th>State</th>
                    </tr>
                </thead>

                <tbody id="#tests-table">
                    {testNodes}
                </tbody>
			</table>
        );
    }
});

var State = React.createClass({
    render: function() {
        var color;

        if (this.props.type == 'running')
        {
           color = 'info';
        }
        else if (this.props.type == 'ok')
        {
           color = 'success';
        }
        else if (this.props.type == 'failed')
        {
           color = 'danger';
        }
        else if (this.props.type == 'queued')
        {
           color = 'default';
        }

        return (
			<span className={"label label-"+color}>{this.props.type}</span>
        );
    }
});

React.render(
    <TestsTable url="/tests/all" pollInterval={2000}/>,
    document.getElementById('container')
);
